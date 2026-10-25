<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('sets the host header correctly for subdomain routing', function (): void {
    Route::get('/debug-host', fn (): array => [
        'host' => request()->getHost(),
        'server_name' => request()->server('SERVER_NAME'),
        'http_host' => request()->server('HTTP_HOST'),
        'headers' => request()->headers->all(),
        'url' => request()->url(),
        'full_url' => request()->fullUrl(),
    ]);

    pest()->browser()->withHost('debug.localhost');

    visit('/debug-host')
        ->assertSee('debug.localhost');
});

it('works with default host when no custom host is set', function (): void {
    Route::get('/debug-default', fn (): string => 'Default host test works');

    // Don't set custom host - should use default
    visit('/debug-default')
        ->assertSee('Default host test works');
});
