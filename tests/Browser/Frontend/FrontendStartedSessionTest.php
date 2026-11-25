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

it('does not give the saved session of the test to a frontend request that starts its own session', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::get('/client', function (): array {
        session()->start();

        return ['session' => session('owner')];
    });

    session()->put('owner', 'test');
    session()->save();

    visit(frontend('/api/client'))
        ->assertSee('"session":null');
});
