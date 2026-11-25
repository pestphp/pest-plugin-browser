<?php

declare(strict_types=1);

use Illuminate\Container\Attributes\Scoped;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

#[Scoped]
final class FrontendScopedAttributeRefreshService
{
    public bool $frontend = false;
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

it('gives the service of the test back to a consumer the request of the frontend registers before it binds a class with the scoped attribute', function (): void {
    $service = app(FrontendScopedAttributeRefreshService::class);

    $consumer = new class
    {
        public ?object $service = null;

        public function setService(object $service): void
        {
            $this->service = $service;
        }
    };

    Route::get('/client', function () use ($consumer): array {
        app()->refresh(FrontendScopedAttributeRefreshService::class, $consumer, 'setService');

        app()->bind(FrontendScopedAttributeRefreshService::class, function (): FrontendScopedAttributeRefreshService {
            $service = new FrontendScopedAttributeRefreshService;
            $service->frontend = true;

            return $service;
        });

        return ['status' => 'bound'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"bound"');

    expect($consumer->service)->toBe($service);
});
