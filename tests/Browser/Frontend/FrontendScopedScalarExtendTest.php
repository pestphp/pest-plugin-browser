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

it('keeps the scalar substitute of the test when a request of the frontend extends it to the same value', function (): void {
    app()->scoped('frontend-scoped-mode', fn (): string => 'real');

    app()->instance('frontend-scoped-mode', 'fake');

    Route::get('/client', function (): array {
        app()->extend('frontend-scoped-mode', fn (string $mode): string => $mode);

        return ['mode' => app('frontend-scoped-mode')];
    });

    visit(frontend('/api/client'))
        ->assertSee('"mode":"fake"');

    expect(app('frontend-scoped-mode'))->toBe('fake');
});

it('keeps the substitute of the test when a request of the frontend replaces it with another value', function (): void {
    app()->scoped('frontend-scoped-mode', fn (): string => 'real');

    app()->instance('frontend-scoped-mode', 'fake');

    Route::get('/client', function (): array {
        app()->instance('frontend-scoped-mode', 'frontend');

        return ['mode' => app('frontend-scoped-mode')];
    });

    visit(frontend('/api/client'))
        ->assertSee('"mode":"frontend"');

    expect(app('frontend-scoped-mode'))->toBe('fake');
});
