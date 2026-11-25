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

it('keeps the route parameters of a request of the test when the frontend sends a request for the same uri under a different method', function (): void {
    Route::get('/items/{id}', fn (string $id): array => ['shown' => $id]);
    Route::post('/items/{id}', fn (string $id): array => ['stored' => $id]);

    $kept = $this->get('/items/1');

    expect($kept->baseRequest->route('id'))->toBe('1');

    Frontend::instance()->url('/');

    $request = new ClientRequest(Frontend::instance()->bridge()->url().'/items/2', 'POST');
    $request->setHeader('x-pest-lane', (string) getmypid());

    $response = HttpClientBuilder::buildDefault()->request($request);

    expect($response->getStatus())->toBe(200)
        ->and($response->getBody()->buffer())->toBe('{"stored":"2"}')
        ->and($kept->baseRequest->route('id'))->toBe('1');
});
