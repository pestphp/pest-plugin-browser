<?php

declare(strict_types=1);

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request as ClientRequest;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

final readonly class FrontendCompiledRoutesNamedLaterController
{
    public function __construct(private Guard $guard) {}

    public function __invoke(): array
    {
        return ['user' => $this->guard->id()];
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

it('gives a request of the frontend no user of the test when compiled routes are loaded and a runtime route gets its name later', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app('router')->setCompiledRoutes(app('router')->getRoutes()->compile());

    Route::put('/client', FrontendCompiledRoutesNamedLaterController::class)->name('client');

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    $this->put('/client')->assertJson(['user' => 7]);

    Frontend::instance()->url('/');

    $request = new ClientRequest(Frontend::instance()->bridge()->url().'/client', 'PUT', '');
    $request->setHeader('x-pest-lane', (string) getmypid());

    $response = HttpClientBuilder::buildDefault()->request($request);

    expect($response->getStatus())->toBe(200)
        ->and($response->getBody()->buffer())->toBe('{"user":null}');
});
