<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
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

it('writes the session of the frontend into the session directory the test configured after forgetting the drivers', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    $first = sys_get_temp_dir().'/pest-browser-sessions-'.bin2hex(random_bytes(4));
    $second = sys_get_temp_dir().'/pest-browser-sessions-'.bin2hex(random_bytes(4));

    File::ensureDirectoryExists($first);
    File::ensureDirectoryExists($second);

    Route::middleware('web')->get('/store', function (): array {
        session(['cart' => 'frontend']);

        return ['stored' => session('cart')];
    });

    config(['session.driver' => 'array']);

    app('session.store');

    config(['session.driver' => 'file', 'session.files' => $first]);

    $bridge = Frontend::instance()->bridge()->url();

    try {
        $page = visit(frontend('/api/store'))
            ->assertSee('"stored":"frontend"');

        expect(File::files($first))->not->toBeEmpty();

        config(['session.files' => $second]);
        session()->forgetDrivers();
        app()->forgetInstance('session.store');

        $page->navigate($bridge.'/store')
            ->assertSee('"stored":"frontend"');

        expect(File::files($second))->not->toBeEmpty();
    } finally {
        File::deleteDirectory($first);
        File::deleteDirectory($second);
    }
});

it('starts a new session of the frontend after the test forgot the session drivers without changing the configuration', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::middleware('web')->get('/store', function (): array {
        session(['cart' => 'frontend']);

        return ['stored' => session('cart')];
    });

    Route::middleware('web')->get('/read', fn (): array => ['cart' => session('cart')]);

    config(['session.driver' => 'file']);

    app('session.store');

    config(['session.driver' => 'array']);

    $bridge = Frontend::instance()->bridge()->url();

    $page = visit(frontend('/api/store'))
        ->navigate($bridge.'/store')
        ->assertSee('"stored":"frontend"');

    session()->forgetDrivers();
    app()->forgetInstance('session.store');

    $page->navigate($bridge.'/read')
        ->assertSee('"cart":null');
});

it('writes the session of the frontend into the new session directory when the test cached the session store outside the manager', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    $first = sys_get_temp_dir().'/pest-browser-sessions-'.bin2hex(random_bytes(4));
    $second = sys_get_temp_dir().'/pest-browser-sessions-'.bin2hex(random_bytes(4));

    File::ensureDirectoryExists($first);
    File::ensureDirectoryExists($second);

    Route::middleware('web')->get('/store', function (): array {
        session(['cart' => 'frontend']);

        return ['stored' => session('cart')];
    });

    config(['session.driver' => 'file', 'session.files' => $first]);

    app('session.store');
    session()->forgetDrivers();

    $bridge = Frontend::instance()->bridge()->url();

    try {
        $page = visit(frontend('/api/store'))
            ->navigate($bridge.'/store')
            ->assertSee('"stored":"frontend"');

        expect(File::files($first))->not->toBeEmpty();

        config(['session.files' => $second]);
        session()->forgetDrivers();

        $page->navigate($bridge.'/store')
            ->assertSee('"stored":"frontend"');

        expect(File::files($second))->not->toBeEmpty();
    } finally {
        File::deleteDirectory($first);
        File::deleteDirectory($second);
    }
});
