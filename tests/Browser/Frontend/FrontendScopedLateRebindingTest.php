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

it('does not tell a consumer the request of the frontend registers after it rebinds the service', function (): void {
    app()->scoped('frontend-scoped-counter', fn (): stdClass => new stdClass);

    app('frontend-scoped-counter');

    $calls = ['before' => 0, 'after' => 0];

    Route::get('/client', function () use (&$calls): array {
        app()->rebinding('frontend-scoped-counter', function () use (&$calls): void {
            $calls['before']++;
        });

        app()->bind('frontend-scoped-counter', fn (): stdClass => (object) ['frontend' => true]);

        app()->rebinding('frontend-scoped-counter', function () use (&$calls): void {
            $calls['after']++;
        });

        return ['status' => 'bound'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"bound"');

    expect($calls['after'])->toBe(0);
});
