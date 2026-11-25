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

it('calls a rebound callback of a scoped service that takes the service by reference', function (): void {
    app()->scoped('frontend-scoped-reference', fn (): string => 'first');

    app('frontend-scoped-reference');

    $seen = [];

    app()->rebinding('frontend-scoped-reference', function ($app, &$service) use (&$seen): void {
        $seen[] = $service;
    });

    Route::get('/client', function (): array {
        app()->instance('frontend-scoped-reference', 'second');

        return ['status' => 'replaced'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"replaced"');

    expect($seen)->toBe(['second', 'first']);
});

it('calls a rebound callback of a scoped service that takes the container by reference', function (): void {
    app()->scoped('frontend-scoped-reference', fn (): string => 'first');

    app('frontend-scoped-reference');

    $seen = [];

    app()->rebinding('frontend-scoped-reference', function (&$app, $service) use (&$seen): void {
        $seen[] = $service;
    });

    Route::get('/client', function (): array {
        app()->instance('frontend-scoped-reference', 'second');

        return ['status' => 'replaced'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"replaced"');

    expect($seen)->toBe(['second', 'first']);
});
