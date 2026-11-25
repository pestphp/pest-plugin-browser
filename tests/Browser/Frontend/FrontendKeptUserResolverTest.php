<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
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

it('keeps the user resolver the test set on its request after a request of the frontend', function (): void {
    Route::get('/ping', fn (): array => ['pong' => true]);

    $request = request();

    $request->setUserResolver(fn (): GenericUser => new GenericUser(['id' => 5]));

    visit(frontend('/api/ping'))
        ->assertSee('"pong":true');

    expect($request->user()?->getAuthIdentifier())->toBe(5);
});
