<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
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

it('gives a request of the frontend no user of the test through a scoped value that the test resolved', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app()->scoped('current-user-id', fn (): mixed => auth()->id());

    Route::get('/client', fn (): array => ['user' => app('current-user-id')]);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    expect(app('current-user-id'))->toBe(7);

    visit(frontend('/api/client'))
        ->assertSee('"user":null');
});
