<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

beforeEach(function (): void {
    pest()->browser()
        ->frontend(FrontendFixture::directory(), 'sleep 1 && exec '.FrontendFixture::command(), Port::find())
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

it('throws in the test the error of a page request that the server answers while the frontend starts', function (): void {
    Route::get('/failing', fn (): never => throw new RuntimeException('page request failed'));

    Route::get('/page', fn (): string => '<p>laravel page</p><script>setTimeout(() => fetch("/failing"), 300)</script>');

    $this->withoutExceptionHandling();

    $page = visit('/page')->assertSee('laravel page');

    frontend('/');

    expect(fn (): Pest\Browser\Api\Webpage => $page->assertSee('laravel page'))
        ->toThrow(RuntimeException::class, 'page request failed');
});
