<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

interface FrontendScopedBeforeResolvingGateway
{
    public function source(): string;
}

final class FrontendScopedBeforeResolvingRealGateway implements FrontendScopedBeforeResolvingGateway
{
    public function source(): string
    {
        return 'real';
    }
}

final class FrontendScopedBeforeResolvingFakeGateway implements FrontendScopedBeforeResolvingGateway
{
    public function source(): string
    {
        return 'mock';
    }
}

final class FrontendScopedBeforeResolvingCheckout
{
    public function __construct(public FrontendScopedBeforeResolvingGateway $gateway) {}
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

it('keeps the mock of a scoped service that the test registers in a before resolving callback', function (): void {
    app()->scoped(FrontendScopedBeforeResolvingGateway::class, FrontendScopedBeforeResolvingRealGateway::class);

    app()->beforeResolving(FrontendScopedBeforeResolvingCheckout::class, function (): void {
        $this->instance(FrontendScopedBeforeResolvingGateway::class, new FrontendScopedBeforeResolvingFakeGateway);
    });

    expect(app(FrontendScopedBeforeResolvingCheckout::class)->gateway->source())->toBe('mock');

    Route::get('/client', fn (FrontendScopedBeforeResolvingGateway $gateway): array => ['gateway' => $gateway->source()]);

    visit(frontend('/api/client'))
        ->assertSee('"gateway":"mock"');
});
