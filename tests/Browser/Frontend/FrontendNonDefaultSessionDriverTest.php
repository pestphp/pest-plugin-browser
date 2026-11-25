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

it('does not give a session store of another driver of the test to a frontend request', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::get('/client', fn (): array => ['session' => session()->driver('file')->get('owner')]);

    session()->driver('file')->put('owner', 'test');

    visit(frontend('/api/client'))
        ->assertSee('"session":null');
});
