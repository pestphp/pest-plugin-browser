<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Guard;
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

it('keeps the guard the test bound in the container after a request of the frontend', function (): void {
    Route::get('/ping', fn (): array => ['pong' => true]);

    $guard = $this->instance('auth.driver', Mockery::mock(Guard::class));

    expect(app(Guard::class))->toBe($guard);

    visit(frontend('/api/ping'))
        ->assertSee('"pong":true');

    expect(app(Guard::class))->toBe($guard);
});
