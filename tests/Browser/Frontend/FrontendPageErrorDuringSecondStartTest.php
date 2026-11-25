<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

beforeEach(function (): void {
    $this->bridgePort = Port::find();
    $this->firstPort = Port::find();

    pest()->browser()
        ->frontend(FrontendFixture::directory(), FrontendFixture::command(), $this->firstPort)
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort($this->bridgePort)
        ->timeout(10);
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopPort($this->firstPort);
    FrontendFixture::stopPort($frontend->port);
    FrontendFixture::stopBridge($frontend->bridge());
});

it('throws in the test the error of a page of the first frontend that the server answers while a second frontend starts', function (): void {
    Route::get('/failing', fn (): never => throw new RuntimeException('page request failed'));

    Route::get('/page', fn (): string => '<p>frontend page</p><script>setTimeout(() => fetch("/api/failing"), 300)</script>');

    $this->withoutExceptionHandling();

    $page = visit(frontend('/api/page'))->assertSee('frontend page');

    pest()->browser()
        ->frontend(FrontendFixture::directory(), 'sleep 1 && exec '.FrontendFixture::command(), Port::find())
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort($this->bridgePort)
        ->timeout(10);

    frontend('/');

    expect(fn (): Pest\Browser\Api\Webpage => $page->assertSee('frontend page'))
        ->toThrow(RuntimeException::class, 'page request failed');
});
