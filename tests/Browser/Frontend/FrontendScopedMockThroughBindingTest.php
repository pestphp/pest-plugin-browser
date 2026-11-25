<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

interface FrontendScopedMockGateway
{
    public function source(): string;
}

final class FrontendScopedMockRealGateway implements FrontendScopedMockGateway
{
    public function source(): string
    {
        return 'real';
    }
}

final class FrontendScopedMockFakeGateway implements FrontendScopedMockGateway
{
    public function source(): string
    {
        return 'mock';
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

it('keeps the mock of a scoped service that the frontend reaches through a different binding across two requests', function (): void {
    app()->scoped(FrontendScopedMockRealGateway::class);
    app()->bind(FrontendScopedMockGateway::class, FrontendScopedMockRealGateway::class);

    $this->instance(FrontendScopedMockRealGateway::class, new FrontendScopedMockFakeGateway);

    Route::get('/client', fn (FrontendScopedMockGateway $gateway): array => ['gateway' => $gateway->source()]);

    visit(frontend('/api/client'))
        ->assertSee('"gateway":"mock"');

    visit(frontend('/api/client'))
        ->assertSee('"gateway":"mock"');
});
