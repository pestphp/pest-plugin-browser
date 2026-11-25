<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;
use function Pest\Laravel\withUnencryptedCookie;

beforeEach(function (): void {
    pest()->browser()
        ->frontend(FrontendFixture::directory(), FrontendFixture::command(), Port::find())
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort(Port::find())
        ->timeout(10);

    Route::get('/cookies', fn (Request $request): array => [
        'cookies' => $request->cookies->all(),
        'host' => $request->getHttpHost(),
    ]);
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopPort($frontend->port);
    FrontendFixture::stopBridge($frontend->bridge());
});

it('serves the backend of this process to the frontend', function (): void {
    visit(frontend('/api/cookies'))
        ->assertSee('"cookies":[]')
        ->assertSee('"host":"127.0.0.1:');

    expect(Frontend::instance()->laneHost())->toBe('pest-w'.getmypid().'.localhost:'.Frontend::instance()->port);
});

it('does not send the cookies of the test with the requests of the frontend', function (): void {
    withUnencryptedCookie('test-cookie', value: 'test value');

    visit(frontend('/api/cookies'))
        ->assertSee('"cookies":[]');

    visit('/cookies')
        ->assertSee('"test-cookie":"test value"');
});

it('gives the requests of the frontend their own session and user', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::middleware('web')->get('/client', fn (): array => [
        'user' => auth()->id(),
        'session' => session('owner'),
    ]);

    $this->actingAs(new GenericUser(['id' => 7]));
    session()->put('owner', 'test');

    visit(frontend('/api/client'))
        ->assertSee('"user":null')
        ->assertSee('"session":null');

    expect(auth()->id())->toBe(7)
        ->and(session('owner'))->toBe('test');

    visit('/client')
        ->assertSee('"user":7')
        ->assertSee('"session":"test"');
});

it('skips the test when the frontend cannot start', function (): void {
    pest()->browser()
        ->frontend(FrontendFixture::directory(), 'exit 1', Port::find())
        ->bridgePort(Port::find())
        ->timeout(5);

    visit(frontend('/'));
})->throws(PHPUnit\Framework\SkippedWithMessageException::class, 'The frontend did not answer');
