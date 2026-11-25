<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

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

it('does not give the test the session store a frontend request resolved when the test had no session driver', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
    config(['session.driver' => null]);

    Route::get('/client', function (): array {
        config(['session.driver' => 'file']);

        app('session.store')->put('cart', 'frontend');

        $cart = app('session.store')->get('cart');

        config(['session.driver' => null]);

        return ['cart' => $cart];
    });

    visit(frontend('/api/client'))
        ->assertSee('"cart":"frontend"');

    config(['session.driver' => 'file']);

    expect(app('session.store')->get('cart'))->toBeNull();
});
