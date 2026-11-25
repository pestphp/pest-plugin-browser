<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

interface FrontendScopedDecoratedInResolvingUser
{
    public function userId(): mixed;
}

final class FrontendScopedDecoratedInResolvingService implements FrontendScopedDecoratedInResolvingUser
{
    public function __construct(public Guard $guard) {}

    public function userId(): mixed
    {
        return $this->guard->id();
    }
}

final class FrontendScopedDecoratedInResolvingDecorator implements FrontendScopedDecoratedInResolvingUser
{
    public function __construct(public FrontendScopedDecoratedInResolvingUser $inner) {}

    public function userId(): mixed
    {
        return $this->inner->userId();
    }
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

it('gives a request of the frontend no user of the test through a scoped service that a resolving callback decorates', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app()->scoped(FrontendScopedDecoratedInResolvingService::class);

    app()->afterResolving(FrontendScopedDecoratedInResolvingService::class, function (FrontendScopedDecoratedInResolvingUser $service, $app): void {
        if ($service instanceof FrontendScopedDecoratedInResolvingDecorator) {
            return;
        }

        $app->extend(FrontendScopedDecoratedInResolvingService::class, fn (FrontendScopedDecoratedInResolvingUser $service): FrontendScopedDecoratedInResolvingUser => new FrontendScopedDecoratedInResolvingDecorator($service));
    });

    Route::get('/client', fn (): array => ['user' => app(FrontendScopedDecoratedInResolvingService::class)->userId()]);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    app(FrontendScopedDecoratedInResolvingService::class);

    expect(app(FrontendScopedDecoratedInResolvingService::class)->userId())->toBe(7);

    visit(frontend('/api/client'))
        ->assertSee('"user":null');
});
