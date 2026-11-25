<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

it('keeps the request guard of the test away from the credentials of a request of the frontend', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
    config(['auth.guards.api' => ['driver' => 'frontend-client']]);

    Auth::viaRequest('frontend-client', fn (Request $request): ?GenericUser => $request->hasHeader('X-Forwarded-Host')
        ? new GenericUser(['id' => 9])
        : null);

    Route::middleware('auth:api')->get('/client', fn (): array => [
        'user' => auth()->id(),
    ]);

    expect(Auth::guard('api')->user())->toBeNull();

    visit(frontend('/api/client'))
        ->assertSee('"user":9');

    expect(Auth::guard('api')->user())->toBeNull();
});
