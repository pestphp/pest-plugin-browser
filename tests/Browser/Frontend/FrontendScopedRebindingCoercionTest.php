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

it('calls a rebound callback of a scoped service with the type coercion of the container', function (): void {
    app()->scoped('frontend-scoped-number', fn (): string => '10');

    app('frontend-scoped-number');

    $seen = [];

    app()->rebinding('frontend-scoped-number', function ($app, int $number) use (&$seen): void {
        $seen[] = $number;
    });

    Route::get('/client', function (): array {
        app()->instance('frontend-scoped-number', '20');

        return ['status' => 'replaced'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"replaced"');

    expect($seen)->toBe([20, 10]);
});
