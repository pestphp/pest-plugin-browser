<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

final readonly class FrontendConstructorGuardController
{
    public function __construct(private Guard $guard) {}

    public function __invoke(): array
    {
        return ['user' => $this->guard->id()];
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

it('gives a request of the frontend no user of the test through a controller that the route keeps', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    Route::get('/client', FrontendConstructorGuardController::class);

    $this->actingAs(new GenericUser(['id' => 7]), 'web');

    visit('/client')
        ->assertSee('"user":7');

    visit(frontend('/api/client'))
        ->assertSee('"user":null');
});
