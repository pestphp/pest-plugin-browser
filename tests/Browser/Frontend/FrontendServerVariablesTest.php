<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

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

it('does not give the server variables of the test to the requests of the frontend', function (): void {
    Route::get('/authorization', fn (Request $request): array => [
        'authorization' => $request->header('authorization'),
    ]);

    $this->withServerVariables(['HTTP_AUTHORIZATION' => 'Bearer test-token']);

    visit(frontend('/api/authorization'))
        ->assertSee('"authorization":null');
});
