<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('can visit non-subdomain routes with subdomain host browser testing', function (): void {
    Route::get('/app-test', fn (): string => '
        <html>
        <head><title>Non Subdomain</title></head>
        <body>
            <h1>Welcome to NON Subdomain</h1>
            <div id="content">This is the non subdomain content</div>
        </body>
        </html>
    ');

    visit('/app-test')
        ->withHost('app.localhost')
        ->assertSee('Welcome to NON Subdomain')
        ->assertSeeIn('#content', 'This is the non subdomain content')
        ->assertTitle('Non Subdomain');
});

it('works with Laravel subdomain style', function (): void {
    // Simulate Laravel Sail subdomain routing pattern
    Route::domain('{subdomain}.localhost')->group(function (): void {
        Route::get('/api/health', fn (): array => [
            'status' => 'ok',
            'subdomain' => request()->route('subdomain'),
            'host' => request()->getHost(),
        ]);
    });

    visit('/api/health')
        ->withHost('api.localhost')
        ->assertSee('"status":"ok"')
        ->assertSee('"subdomain":"api"')
        ->assertSee('"host":"api.localhost"');
});
