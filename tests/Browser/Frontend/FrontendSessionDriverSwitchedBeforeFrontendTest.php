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

it('keeps the session of the frontend between its requests after the test switched the session driver', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::middleware('web')->get('/store', function (): array {
        session(['cart' => 'frontend']);

        return ['stored' => session('cart')];
    });

    Route::middleware('web')->get('/read', fn (): array => [
        'cart' => session('cart'),
    ]);

    config(['session.driver' => 'file']);

    app('session.store');

    config(['session.driver' => 'array']);

    $bridge = Frontend::instance()->bridge()->url();

    visit(frontend('/api/store'))
        ->assertSee('"stored":"frontend"')
        ->navigate($bridge.'/store')
        ->assertSee('"stored":"frontend"')
        ->navigate($bridge.'/read')
        ->assertSee('"cart":"frontend"');
});

it('keeps the session of the frontend when the test resolves the same session driver between its requests', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::middleware('web')->get('/store', function (): array {
        session(['cart' => 'frontend']);

        return ['stored' => session('cart')];
    });

    Route::middleware('web')->get('/read', fn (): array => [
        'cart' => session('cart'),
    ]);

    config(['session.driver' => 'file']);

    app('session.store');

    config(['session.driver' => 'array']);

    $bridge = Frontend::instance()->bridge()->url();

    $page = visit(frontend('/api/store'))
        ->assertSee('"stored":"frontend"')
        ->navigate($bridge.'/store')
        ->assertSee('"stored":"frontend"');

    session()->driver('array')->put('cart', 'test');

    $page->navigate($bridge.'/read')
        ->assertSee('"cart":"frontend"');

    expect(session()->driver('array')->get('cart'))->toBe('test');
});

it('keeps the session of the frontend when the session configuration holds a closure', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::middleware('web')->get('/store', function (): array {
        session(['cart' => 'frontend']);

        return ['stored' => session('cart')];
    });

    Route::middleware('web')->get('/read', fn (): array => [
        'cart' => session('cart'),
    ]);

    config(['session.driver' => 'file', 'session.factory' => static fn (): null => null]);

    app('session.store');

    config(['session.driver' => 'array']);

    $bridge = Frontend::instance()->bridge()->url();

    visit(frontend('/api/store'))
        ->assertSee('"stored":"frontend"')
        ->navigate($bridge.'/store')
        ->assertSee('"stored":"frontend"')
        ->navigate($bridge.'/read')
        ->assertSee('"cart":"frontend"');
});
