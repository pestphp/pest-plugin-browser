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
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopPort($frontend->port);
    FrontendFixture::stopBridge($frontend->bridge());
});

it('tells a consumer the substitute of the test once the url of the test is back', function (): void {
    app()->scoped('frontend-scoped-mode', fn (): string => 'real');

    app()->instance('frontend-scoped-mode', 'fake');

    $consumer = new class
    {
        public mixed $url = null;

        public function setMode(string $mode): void
        {
            $this->url = config('app.url');
        }
    };

    app()->refresh('frontend-scoped-mode', $consumer, 'setMode');

    Route::get('/client', function (): array {
        app()->instance('frontend-scoped-mode', 'frontend');

        return ['url' => config('app.url')];
    });

    $testUrl = config('app.url');

    visit(frontend('/api/client'))
        ->assertSee(Frontend::instance()->laneHost());

    expect($consumer->url)->toBe($testUrl);
});
