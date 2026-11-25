<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

#[Scoped]
final readonly class FrontendScopedAttributeGuardHolder
{
    public function __construct(public Guard $guard) {}
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

it('gives two requests of the frontend no user of the test through a class with the scoped attribute that the test resolved first', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::get('/client', fn (FrontendScopedAttributeGuardHolder $holder): array => ['user' => $holder->guard->id()]);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    $holder = app(FrontendScopedAttributeGuardHolder::class);

    expect($holder->guard->id())->toBe(7);

    visit(frontend('/api/client'))
        ->assertSee('"user":null');

    visit(frontend('/api/client'))
        ->assertSee('"user":null');

    expect(app(FrontendScopedAttributeGuardHolder::class))->toBe($holder);
});
