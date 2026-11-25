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

it('calls again every rebound callback of a scoped service in the order the container called them', function (): void {
    app()->scoped('frontend-scoped-writer', fn (): stdClass => (object) ['name' => 'test']);

    app('frontend-scoped-writer');

    $calls = [];

    $first = function () use (&$calls): void {
        $calls[] = 'A';
    };

    app()->rebinding('frontend-scoped-writer', $first);
    app()->rebinding('frontend-scoped-writer', function () use (&$calls): void {
        $calls[] = 'B';
    });
    app()->rebinding('frontend-scoped-writer', $first);

    Route::get('/client', function (): array {
        app()->instance('frontend-scoped-writer', (object) ['name' => 'frontend']);

        return ['status' => 'replaced'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"replaced"');

    expect($calls)->toBe(['A', 'B', 'A', 'A', 'B', 'A']);
});
