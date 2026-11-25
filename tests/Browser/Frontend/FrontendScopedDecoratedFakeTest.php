<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

interface FrontendScopedDecoratedFakeSource
{
    public function source(): string;
}

final class FrontendScopedDecoratedFakeService implements FrontendScopedDecoratedFakeSource
{
    public function __construct(public string $label = 'real') {}

    public function source(): string
    {
        return $this->label;
    }
}

final readonly class FrontendScopedDecoratedFakeDecorator implements FrontendScopedDecoratedFakeSource
{
    public function __construct(public FrontendScopedDecoratedFakeSource $inner) {}

    public function source(): string
    {
        return 'decorated '.$this->inner->source();
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

it('keeps the fake of the test that a resolving callback decorates during a contextual build', function (): void {
    app()->scoped(FrontendScopedDecoratedFakeService::class);

    app()->instance(FrontendScopedDecoratedFakeService::class, new FrontendScopedDecoratedFakeService('fake'));

    app()->afterResolving(FrontendScopedDecoratedFakeService::class, function (FrontendScopedDecoratedFakeSource $service, $app): void {
        $app->extend(FrontendScopedDecoratedFakeService::class, fn (FrontendScopedDecoratedFakeSource $stored): FrontendScopedDecoratedFakeSource => $stored instanceof FrontendScopedDecoratedFakeDecorator ? $stored : new FrontendScopedDecoratedFakeDecorator($stored));
    });

    app()->makeWith(FrontendScopedDecoratedFakeService::class, ['label' => 'contextual']);

    Route::get('/client', fn (): array => ['source' => app(FrontendScopedDecoratedFakeService::class)->source()]);

    visit(frontend('/api/client'))
        ->assertSee('"source":"decorated fake"');
});
