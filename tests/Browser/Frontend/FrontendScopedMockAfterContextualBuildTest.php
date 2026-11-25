<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

interface FrontendScopedContextualGateway
{
    public function source(): string;
}

final class FrontendScopedContextualRealGateway implements FrontendScopedContextualGateway
{
    public function source(): string
    {
        return 'real';
    }
}

final class FrontendScopedContextualFakeGateway implements FrontendScopedContextualGateway
{
    public function source(): string
    {
        return 'mock';
    }
}

final readonly class FrontendScopedContextualConsumer
{
    public function __construct(public FrontendScopedContextualRealGateway $gateway) {}
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

it('keeps across two requests the mock of a scoped service that the test registers after a contextual build of the service', function (): void {
    app()->scoped(FrontendScopedContextualRealGateway::class);
    app()->bind(FrontendScopedContextualGateway::class, FrontendScopedContextualRealGateway::class);
    app()->when(FrontendScopedContextualConsumer::class)
        ->needs(FrontendScopedContextualRealGateway::class)
        ->give(fn (): FrontendScopedContextualRealGateway => new FrontendScopedContextualRealGateway);

    expect(app(FrontendScopedContextualConsumer::class)->gateway->source())->toBe('real');

    $this->instance(FrontendScopedContextualRealGateway::class, new FrontendScopedContextualFakeGateway);

    Route::get('/client', fn (FrontendScopedContextualGateway $gateway): array => ['gateway' => $gateway->source()]);

    visit(frontend('/api/client'))
        ->assertSee('"gateway":"mock"');

    visit(frontend('/api/client'))
        ->assertSee('"gateway":"mock"');
});
