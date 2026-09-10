<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pest\Browser\ServerManager;

it('exposes the canonical host on request()->url() when withHost is set', function (): void {
    Route::domain('app.localhost')->get('/canonical/request-url', fn (Request $request) => $request->url());

    pest()->browser()->withHost('app.localhost');

    $port = ServerManager::instance()->http()->port; // @phpstan-ignore-line

    visit('/canonical/request-url')
        ->assertUrlIs("http://app.localhost:{$port}/canonical/request-url")
        ->assertSee("http://app.localhost:{$port}/canonical/request-url");
});

it('generates route() URLs against the canonical host when withHost is set', function (): void {
    Route::domain('app.localhost')->as('canonical.test')->get('/canonical/route-url', fn (): string => route('canonical.test'));

    pest()->browser()->withHost('app.localhost');

    $port = ServerManager::instance()->http()->port; // @phpstan-ignore-line

    visit('/canonical/route-url')
        ->assertSee("http://app.localhost:{$port}/canonical/route-url");

    expect(route('canonical.test'))->toBe("http://app.localhost:{$port}/canonical/route-url");
});

it('reverts to the bound IP origin when withHost(null) clears the configured host', function (): void {
    Route::as('canonical.no-host')->get('/canonical/no-host', fn (): string => route('canonical.no-host'));

    // Ensure no leaking host from earlier tests.
    pest()->browser()->withHost(null);

    $port = ServerManager::instance()->http()->port; // @phpstan-ignore-line

    visit('/canonical/no-host')
        ->assertSee("http://127.0.0.1:{$port}/canonical/no-host");

    expect(route('canonical.no-host'))->toBe("http://127.0.0.1:{$port}/canonical/no-host");
});

it('updates the URL generator immediately when withHost changes mid-test', function (): void {
    Route::domain('first.localhost')->as('canonical.first')->get('/canonical/first', fn (): string => route('canonical.first'));
    Route::domain('second.localhost')->as('canonical.second')->get('/canonical/second', fn (): string => route('canonical.second'));

    $port = ServerManager::instance()->http()->port; // @phpstan-ignore-line

    visit('/canonical/first')
        ->withHost('first.localhost')
        ->assertSee("http://first.localhost:{$port}/canonical/first");

    expect(route('canonical.first'))->toBe("http://first.localhost:{$port}/canonical/first");

    // Per-request resync should also surface the new host on the page itself.
    visit('/canonical/second')
        ->withHost('second.localhost')
        ->assertSee("http://second.localhost:{$port}/canonical/second");

    expect(route('canonical.second'))->toBe("http://second.localhost:{$port}/canonical/second");
});
