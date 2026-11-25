<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

interface FrontendScopedExtendedGateway
{
    public function source(): string;
}

final class FrontendScopedExtendedRealGateway implements FrontendScopedExtendedGateway
{
    public function source(): string
    {
        return 'real';
    }
}

final class FrontendScopedExtendedFakeGateway implements FrontendScopedExtendedGateway
{
    public function source(): string
    {
        return 'fake';
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

it('keeps the substitute that the test installs with extend on a resolved scoped service', function (): void {
    app()->scoped(FrontendScopedExtendedGateway::class, FrontendScopedExtendedRealGateway::class);

    expect(app(FrontendScopedExtendedGateway::class)->source())->toBe('real');

    app()->extend(FrontendScopedExtendedGateway::class, fn (FrontendScopedExtendedGateway $gateway): FrontendScopedExtendedGateway => new FrontendScopedExtendedFakeGateway);

    expect(app(FrontendScopedExtendedGateway::class)->source())->toBe('fake');

    Route::get('/client', fn (FrontendScopedExtendedGateway $gateway): array => ['gateway' => $gateway->source()]);

    visit(frontend('/api/client'))
        ->assertSee('"gateway":"fake"');
});
