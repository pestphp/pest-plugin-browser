<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

interface FrontendScopedFailedBuildGateway
{
    public function source(): string;
}

final class FrontendScopedFailedBuildRealGateway implements FrontendScopedFailedBuildGateway
{
    public function source(): string
    {
        return 'real';
    }
}

final class FrontendScopedFailedBuildFakeGateway implements FrontendScopedFailedBuildGateway
{
    public function source(): string
    {
        return 'mock';
    }
}

final readonly class FrontendScopedFailedBuildConsumer
{
    public function __construct(public FrontendScopedFailedBuildRealGateway $gateway)
    {
        throw new RuntimeException('The consumer cannot be built.');
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

it('keeps across two requests the mock of a scoped service that the test registers after a build that failed', function (): void {
    app()->scoped(FrontendScopedFailedBuildRealGateway::class);
    app()->bind(FrontendScopedFailedBuildGateway::class, FrontendScopedFailedBuildRealGateway::class);

    expect(fn (): mixed => app(FrontendScopedFailedBuildConsumer::class))->toThrow(RuntimeException::class);

    $this->instance(FrontendScopedFailedBuildRealGateway::class, new FrontendScopedFailedBuildFakeGateway);

    Route::get('/client', fn (FrontendScopedFailedBuildGateway $gateway): array => ['gateway' => $gateway->source()]);

    visit(frontend('/api/client'))
        ->assertSee('"gateway":"mock"');

    visit(frontend('/api/client'))
        ->assertSee('"gateway":"mock"');
});

it('gives a request of the frontend its own scoped service when the test resolved the service in a build that failed', function (): void {
    app()->scoped(FrontendScopedFailedBuildRealGateway::class);

    expect(fn (): mixed => app(FrontendScopedFailedBuildConsumer::class))->toThrow(RuntimeException::class);

    $gateway = app(FrontendScopedFailedBuildRealGateway::class);

    Route::get('/client', fn (FrontendScopedFailedBuildRealGateway $gateway): array => ['object' => 'id '.spl_object_id($gateway)]);

    visit(frontend('/api/client'))
        ->assertDontSee('"object":"id '.spl_object_id($gateway).'"');

    expect(app(FrontendScopedFailedBuildRealGateway::class))->toBe($gateway);
});
