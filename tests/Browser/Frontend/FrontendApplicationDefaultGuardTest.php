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

it('resolves the user of a request of the frontend with the default guard of the application after the test acts as a user of a different guard', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web' => ['driver' => 'frontend-client']]);
    config(['auth.guards.admin' => ['driver' => 'frontend-admin']]);

    Auth::viaRequest('frontend-client', fn (Request $request): GenericUser => new GenericUser(['id' => 9]));
    Auth::viaRequest('frontend-admin', fn (Request $request): ?GenericUser => null);

    Route::get('/client', fn (): array => [
        'user' => auth()->id(),
    ]);

    $this->actingAs(new GenericUser(['id' => 7]), 'admin');

    visit(frontend('/api/client'))
        ->assertSee('"user":9');
});
