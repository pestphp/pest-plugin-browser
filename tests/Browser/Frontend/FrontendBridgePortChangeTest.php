<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

beforeEach(function (): void {
    $this->frontendPort = Port::find();
    $this->bridges = [];

    Route::get('/cookies', fn (Request $request): array => ['cookies' => $request->cookies->all()]);
});

afterEach(function (): void {
    Frontend::reset();
    FrontendFixture::stopPort($this->frontendPort);

    foreach ($this->bridges as $bridge) {
        FrontendFixture::stopBridge($bridge);
    }
});

it('forwards the requests of a running frontend to the bridge of the new configuration', function (): void {
    pest()->browser()
        ->frontend(FrontendFixture::directory(), FrontendFixture::command(), $this->frontendPort)
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort(Port::find())
        ->timeout(10);

    Frontend::instance()->url('/');
    $this->bridges[] = Frontend::instance()->bridge();

    pest()->browser()
        ->frontend(FrontendFixture::directory(), FrontendFixture::command(), $this->frontendPort)
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort(Port::find())
        ->timeout(10);

    $this->bridges[] = Frontend::instance()->bridge();

    visit(frontend('/api/cookies'))
        ->assertSee('"cookies":[]');
});
