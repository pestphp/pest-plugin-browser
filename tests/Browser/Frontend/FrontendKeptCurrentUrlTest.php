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

it('keeps the current URL of the test after a request of the frontend', function (): void {
    Route::get('/ping', fn (): array => ['pong' => true]);
    Route::get('/profile', fn (): string => 'profile');

    $url = frontend('/api/ping');
    $page = visit($url)->assertSee('"pong":true');

    $this->get('/profile?tab=data')->assertOk();

    $current = url()->full();

    expect($current)->toEndWith('/profile?tab=data')
        ->and(request()->fullUrl())->toBe($current);

    $page->navigate($url)->assertSee('"pong":true');

    expect(request()->fullUrl())->toBe($current)
        ->and(url()->full())->toBe($current);
});
