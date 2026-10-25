<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Playwright\Playwright;

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

    pest()->browser()->withHost('app.localhost');

    visit('/app-test')
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

    pest()->browser()->withHost('api.localhost');

    visit('/api/health')
        ->assertSee('"status":"ok"')
        ->assertSee('"subdomain":"api"')
        ->assertSee('"host":"api.localhost"');
});

it('Can chain withHost on visit', function (): void {
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

it('Chaining withHost will not override global host', function (): void {
    Route::domain('{subdomain}.localhost')->group(function (): void {
        Route::get('/api/health', fn (): array => [
            'subdomain' => request()->route('subdomain'),
            'host' => request()->getHost(),
        ]);
    });

    Route::get('/', fn (): array => [
        'host' => request()->getHost(),
    ]);

    // Set global host: test.domain
    pest()->browser()->withHost('test.domain');

    // 1. Visit withHost: api.localhost
    visit('/api/health')
        ->withHost('api.localhost')
        ->assertSee('"host":"api.localhost"')
        ->assertDontSee('test.domain');

    // 2. Visit without withHost: should use global host "test.domain"
    visit('/')
        ->assertSee('"host":"test.domain"')
        ->assertDontSee('api.localhost');
});

it('uses the first withHost when chained multiple times', function (): void {
    // Because of the spread operator "...$this->options" in PendingAwaitablePage
    Route::domain('{subdomain}.localhost')->group(function (): void {
        Route::get('/api/info', fn (): array => [
            'subdomain' => request()->route('subdomain'),
            'host' => request()->getHost(),
        ]);
    });

    visit('/api/info')
        ->withHost('first.localhost')
        ->withHost('api.localhost')
        ->assertSee('"host":"first.localhost"')
        ->assertSee('"subdomain":"first"')
        ->assertDontSee('api.localhost');
});

it('withHost works correctly when combined with other options', function (): void {
    Route::domain('{subdomain}.localhost')->group(function (): void {
        Route::get('/api/locale', fn (): array => [
            'host' => request()->getHost(),
            'subdomain' => request()->route('subdomain'),
        ]);
    });

    visit('/api/locale')
        ->withHost('api.localhost')
        ->inDarkMode()
        ->assertSee('"host":"api.localhost"')
        ->assertSee('"subdomain":"api"');
});

it('correctly alternates hosts across multiple visits', function (): void {
    Route::domain('{subdomain}.localhost')->group(function (): void {
        Route::get('/check', fn (): array => [
            'host' => request()->getHost(),
            'subdomain' => request()->route('subdomain'),
        ]);
    });

    // Visit 1: api.localhost
    visit('/check')
        ->withHost('api.localhost')
        ->assertSee('"host":"api.localhost"')
        ->assertSee('"subdomain":"api"');

    // Visit 2: admin.localhost
    visit('/check')
        ->withHost('admin.localhost')
        ->assertSee('"host":"admin.localhost"')
        ->assertSee('"subdomain":"admin"');

    // Visit 3: back to api.localhost
    visit('/check')
        ->withHost('api.localhost')
        ->assertSee('"host":"api.localhost"')
        ->assertSee('"subdomain":"api"');
});

it('withHost works when no global host is configured', function (): void {
    Route::domain('{subdomain}.localhost')->group(function (): void {
        Route::get('/standalone', fn (): array => [
            'host' => request()->getHost(),
            'subdomain' => request()->route('subdomain'),
        ]);
    });

    // No pest()->browser()->withHost() call - just use per-visit withHost
    visit('/standalone')
        ->withHost('custom.localhost')
        ->assertSee('"host":"custom.localhost"')
        ->assertSee('"subdomain":"custom"');
});

it('restores global host even when page creation encounters issues', function (): void {
    Route::get('/restore-check', fn (): array => [
        'host' => request()->getHost(),
    ]);

    $originalHost = 'original.localhost';
    pest()->browser()->withHost($originalHost);

    // Perform a visit with a different host
    visit('/restore-check')
        ->withHost('temporary.localhost')
        ->assertSee('"host":"temporary.localhost"');

    // Verify global host is still the original after the visit
    expect(Playwright::host())->toBe($originalHost);

    // Verify next visit without withHost uses the global host
    visit('/restore-check')
        ->assertSee('"host":"original.localhost"');
});
