<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
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

it('keeps the asset origin and the URL origin of the test after a request of the frontend', function (): void {
    Route::get('/ping', fn (): array => ['pong' => true]);

    $url = frontend('/api/ping');
    visit($url)->assertSee('"pong":true');

    URL::useAssetOrigin('https://static.example.test');
    URL::useOrigin('https://app.example.test');

    $asset = asset('logo.svg');
    $home = url('/home');

    expect($asset)->toBe('https://static.example.test/logo.svg')
        ->and($home)->toEndWith('://app.example.test/home');

    visit($url)->assertSee('"pong":true');

    expect(asset('logo.svg'))->toBe($asset)
        ->and(url('/home'))->toBe($home);
});

it('keeps the current route of the test after a request of the frontend', function (): void {
    Route::get('/ping', fn (): array => ['pong' => true])->name('ping');
    Route::get('/profile', fn (): string => 'profile')->name('profile');

    $url = frontend('/api/ping');
    visit($url)->assertSee('"pong":true');

    $this->get('/profile')->assertOk();

    expect(Route::currentRouteName())->toBe('profile');

    visit($url)->assertSee('"pong":true');

    expect(Route::currentRouteName())->toBe('profile')
        ->and(request()->route()?->getName())->toBe('profile')
        ->and(app(Illuminate\Routing\Route::class)->getName())->toBe('profile');
});

it('keeps the default route parameters of the test after a request of the frontend changes them', function (): void {
    Route::get('/{locale}/profile', fn (): string => 'profile')->name('localized.profile');
    Route::get('/ping', function (): array {
        URL::defaults(['locale' => 'en', 'team' => 'frontend']);

        return ['pong' => true];
    });

    app('router')->getRoutes()->refreshNameLookups();

    URL::defaults(['locale' => 'es']);

    $profile = route('localized.profile');

    visit(frontend('/api/ping'))->assertSee('"pong":true');

    expect(route('localized.profile'))->toBe($profile)
        ->and(URL::getDefaultParameters())->toBe(['locale' => 'es']);
});

it('keeps the scheme and the URL formatters of the test after a request of the frontend changes them', function (): void {
    Route::get('/ping', function (): array {
        URL::forceScheme('https');
        URL::formatHostUsing(fn (string $host): string => 'frontend.example.test');

        return ['pong' => true];
    });

    $home = url('/home');

    visit(frontend('/api/ping'))->assertSee('"pong":true');

    expect(url('/home'))->toBe($home);
});

it('keeps the routes the URL generator of the test resolves names with after a request of the frontend replaces them', function (): void {
    Route::get('/profile', fn (): string => 'profile')->name('profile');
    Route::get('/ping', function (): array {
        URL::setRoutes(new Illuminate\Routing\RouteCollection);

        return ['pong' => true];
    });

    app('router')->getRoutes()->refreshNameLookups();

    $profile = route('profile');

    visit(frontend('/api/ping'))->assertSee('"pong":true');

    expect(route('profile'))->toBe($profile);
});

it('keeps the routes of the router of the test after a request of the frontend replaces them', function (): void {
    Route::get('/profile', fn (): string => 'profile')->name('profile');
    Route::get('/ping', function (): array {
        Route::setRoutes(new Illuminate\Routing\RouteCollection);

        return ['pong' => true];
    });

    app('router')->getRoutes()->refreshNameLookups();

    $routes = app('router')->getRoutes();
    $profile = route('profile');

    visit(frontend('/api/ping'))->assertSee('"pong":true');

    Route::get('/later', fn (): string => 'later')->name('later');
    app('router')->getRoutes()->refreshNameLookups();

    expect(app('router')->getRoutes())->toBe($routes)
        ->and(app('routes'))->toBe($routes)
        ->and(route('profile'))->toBe($profile)
        ->and(route('later'))->toEndWith('/later');

    $this->get('/profile')->assertOk()->assertSee('profile');
});
