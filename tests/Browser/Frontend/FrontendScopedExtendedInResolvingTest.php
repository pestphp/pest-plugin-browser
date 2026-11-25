<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

final class FrontendScopedExtendedInResolvingHolder
{
    public bool $configured = false;

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

it('gives a request of the frontend no user of the test through a scoped service that a resolving callback extends', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app()->scoped(FrontendScopedExtendedInResolvingHolder::class);

    app()->afterResolving(FrontendScopedExtendedInResolvingHolder::class, function (FrontendScopedExtendedInResolvingHolder $holder, $app): void {
        $app->extend(FrontendScopedExtendedInResolvingHolder::class, function (FrontendScopedExtendedInResolvingHolder $holder): FrontendScopedExtendedInResolvingHolder {
            $holder->configured = true;

            return $holder;
        });
    });

    Route::get('/client', fn (FrontendScopedExtendedInResolvingHolder $holder): array => ['user' => $holder->guard->id()]);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    expect(app(FrontendScopedExtendedInResolvingHolder::class)->guard->id())->toBe(7);

    visit(frontend('/api/client'))
        ->assertSee('"user":null');
});
