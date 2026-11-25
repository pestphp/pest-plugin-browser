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
        ->forwardLaneHost()
        ->timeout(10);

    Route::get('/origin', fn (): array => ['url' => url('/')]);
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopPort($frontend->port);
    FrontendFixture::stopBridge($frontend->bridge());
});

it('keeps the generated URLs of the test on the backend after a request of the frontend', function (): void {
    visit('/origin');

    $backendUrl = url('/');

    visit(frontend('/api/origin'))
        ->assertSee(Frontend::instance()->laneHost());

    expect(url('/'))->toBe($backendUrl);
});
