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

final readonly class FrontendMiddlewareMethodChangeController
{
    public function __construct(private Guard $guard) {}

    public function __invoke(): array
    {
        return ['user' => $this->guard->id()];
    }
}

final class FrontendMiddlewareMethodChangeMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->getMethod() === 'POST') {
            $request->setMethod('PUT');
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

it('gives a request of the frontend no user of the test when a global middleware changes its method', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app(Kernel::class)->pushMiddleware(FrontendMiddlewareMethodChangeMiddleware::class);

    Route::put('/client', FrontendMiddlewareMethodChangeController::class);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    $this->put('/client')->assertJson(['user' => 7]);

    Frontend::instance()->url('/');

    $request = new ClientRequest(Frontend::instance()->bridge()->url().'/client', 'POST', '');
    $request->setHeader('x-pest-lane', (string) getmypid());

    $response = HttpClientBuilder::buildDefault()->request($request);

    expect($response->getStatus())->toBe(200)
        ->and($response->getBody()->buffer())->toBe('{"user":null}');
});
