<?php

declare(strict_types=1);

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request as ClientRequest;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

final readonly class FrontendMiddlewareUriRewriteController
{
    public function __construct(private Guard $guard) {}

    public function __invoke(): array
    {
        return ['user' => $this->guard->id()];
    }
}

final class FrontendMiddlewareUriRewriteMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (str_starts_with($request->getPathInfo(), '/es/')) {
            $request->server->set('REQUEST_URI', mb_substr($request->getRequestUri(), 3));
            $request = Request::createFrom($request->duplicate(server: $request->server->all()));
        }

        return $next($request);
    }
}

beforeEach(function (): void {
    pest()->browser()
        ->frontend(FrontendFixture::directory(), FrontendFixture::command(), Port::find())
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort(Port::find())
        ->timeout(10);
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopPort($frontend->port);
    FrontendFixture::stopBridge($frontend->bridge());
});

it('gives a request of the frontend no user of the test when a global middleware rewrites its URI', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app(Kernel::class)->pushMiddleware(FrontendMiddlewareUriRewriteMiddleware::class);

    Route::put('/client', FrontendMiddlewareUriRewriteController::class);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    $this->put('/client')->assertJson(['user' => 7]);

    Frontend::instance()->url('/');

    $request = new ClientRequest(Frontend::instance()->bridge()->url().'/es/client', 'PUT', '');
    $request->setHeader('x-pest-lane', (string) getmypid());

    $response = HttpClientBuilder::buildDefault()->request($request);

    expect($response->getStatus())->toBe(200)
        ->and($response->getBody()->buffer())->toBe('{"user":null}');
});
