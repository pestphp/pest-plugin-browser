<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

final class FrontendScopedFakeInOwnBeforeResolvingService
{
    public function __construct(public string $label = 'real') {}
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

it('keeps the fake of a built scoped service that the test registers in a before resolving callback of that service', function (): void {
    app()->scoped(FrontendScopedFakeInOwnBeforeResolvingService::class);

    app(FrontendScopedFakeInOwnBeforeResolvingService::class);

    $fake = new FrontendScopedFakeInOwnBeforeResolvingService('fake');

    app()->beforeResolving(FrontendScopedFakeInOwnBeforeResolvingService::class, function (string $abstract, array $parameters) use ($fake): void {
        if ($parameters !== []) {
            app()->instance(FrontendScopedFakeInOwnBeforeResolvingService::class, $fake);
        }
    });

    app()->makeWith(FrontendScopedFakeInOwnBeforeResolvingService::class, ['label' => 'contextual']);

    Route::get('/client', fn (): array => ['source' => app(FrontendScopedFakeInOwnBeforeResolvingService::class)->label]);

    visit(frontend('/api/client'))
        ->assertSee('"source":"fake"');
});

it('keeps the fake of a built scoped service that the test registers in a resolving callback of that service', function (): void {
    app()->scoped(FrontendScopedFakeInOwnBeforeResolvingService::class);

    app(FrontendScopedFakeInOwnBeforeResolvingService::class);

    $fake = new FrontendScopedFakeInOwnBeforeResolvingService('fake');

    app()->afterResolving(FrontendScopedFakeInOwnBeforeResolvingService::class, function (FrontendScopedFakeInOwnBeforeResolvingService $service) use ($fake): void {
        if ($service->label === 'contextual') {
            app()->instance(FrontendScopedFakeInOwnBeforeResolvingService::class, $fake);
        }
    });

    app()->makeWith(FrontendScopedFakeInOwnBeforeResolvingService::class, ['label' => 'contextual']);

    Route::get('/client', fn (): array => ['source' => app(FrontendScopedFakeInOwnBeforeResolvingService::class)->label]);

    visit(frontend('/api/client'))
        ->assertSee('"source":"fake"');
});
