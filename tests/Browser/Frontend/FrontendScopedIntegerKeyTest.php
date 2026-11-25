<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

final readonly class FrontendScopedIntegerKeyGuardHolder
{
    public function __construct(public Guard $guard) {}
}

final class FrontendScopedIntegerKeyCounter
{
    public static int $built = 0;
}

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

it('gives two requests of the frontend no user of the test through a scoped service with an integer key', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app()->scoped('123', fn (): FrontendScopedIntegerKeyGuardHolder => new FrontendScopedIntegerKeyGuardHolder(app(Guard::class)));

    Route::get('/client', fn (): array => ['user' => app('123')->guard->id()]);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    $holder = app('123');

    expect($holder->guard->id())->toBe(7);

    visit(frontend('/api/client'))
        ->assertSee('"user":null');

    visit(frontend('/api/client'))
        ->assertSee('"user":null');

    expect(app('123'))->toBe($holder);
});

it('forgets after a request of the frontend the scoped service with an integer key that the request built', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    FrontendScopedIntegerKeyCounter::$built = 0;

    app()->scoped('123', fn (): int => ++FrontendScopedIntegerKeyCounter::$built);

    Route::get('/client', fn (): array => ['built' => 'number '.app('123')]);

    visit(frontend('/api/client'))
        ->assertSee('"built":"number 1"');

    visit(frontend('/api/client'))
        ->assertSee('"built":"number 2"');
});
