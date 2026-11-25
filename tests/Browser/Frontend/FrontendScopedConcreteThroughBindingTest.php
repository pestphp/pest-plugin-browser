<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

interface FrontendScopedConcreteHolder
{
    public function user(): mixed;
}

final readonly class FrontendScopedConcreteGuardHolder implements FrontendScopedConcreteHolder
{
    public function __construct(private Guard $guard) {}

    public function user(): mixed
    {
        return $this->guard->id();
    }
}

final class FrontendScopedConcreteExtenderDependency {}

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

it('gives two requests of the frontend no user of the test through a scoped concrete that the test resolved through a binding', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app()->scoped(FrontendScopedConcreteGuardHolder::class);
    app()->bind(FrontendScopedConcreteHolder::class, FrontendScopedConcreteGuardHolder::class);

    Route::get('/client', fn (FrontendScopedConcreteHolder $holder): array => ['user' => $holder->user()]);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    $holder = app(FrontendScopedConcreteHolder::class);

    expect($holder->user())->toBe(7);

    visit(frontend('/api/client'))
        ->assertSee('"user":null');

    visit(frontend('/api/client'))
        ->assertSee('"user":null');

    expect(app(FrontendScopedConcreteHolder::class))->toBe($holder);
});

it('gives a request of the frontend no user of the test through a scoped concrete when an extender of the binding resolves a service', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app()->scoped(FrontendScopedConcreteGuardHolder::class);
    app()->bind(FrontendScopedConcreteHolder::class, FrontendScopedConcreteGuardHolder::class);
    app()->extend(FrontendScopedConcreteHolder::class, function (FrontendScopedConcreteHolder $holder): FrontendScopedConcreteHolder {
        app(FrontendScopedConcreteExtenderDependency::class);

        return $holder;
    });

    Route::get('/client', fn (FrontendScopedConcreteHolder $holder): array => ['user' => $holder->user()]);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    expect(app(FrontendScopedConcreteHolder::class)->user())->toBe(7);

    visit(frontend('/api/client'))
        ->assertSee('"user":null');
});
