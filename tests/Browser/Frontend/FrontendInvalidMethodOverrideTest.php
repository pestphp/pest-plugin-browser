<?php

declare(strict_types=1);

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

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

it('answers a form of the frontend with an invalid spoofed method as Laravel answers it', function (): void {
    Route::put('/spoofed', fn (): array => ['method' => 'put']);

    Frontend::instance()->url('/');

    $request = new ClientRequest(Frontend::instance()->bridge()->url().'/spoofed', 'POST', '_method=PUT!');
    $request->setHeader('content-type', 'application/x-www-form-urlencoded');
    $request->setHeader('x-pest-lane', (string) getmypid());

    $response = HttpClientBuilder::buildDefault()->request($request);

    expect($response->getStatus())->toBe(400);
});
