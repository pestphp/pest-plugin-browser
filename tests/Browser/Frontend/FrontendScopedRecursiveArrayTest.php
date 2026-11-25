<?php

declare(strict_types=1);

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

it('answers a request of the frontend when the test and the request resolve a scoped array that holds a reference to itself', function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    app()->scoped('graph', function (): array {
        $graph = ['name' => 'tree'];
        $graph['self'] = &$graph;

        return $graph;
    });

    expect(app('graph')['name'])->toBe('tree');

    Route::get('/client', fn (): array => ['name' => app('graph')['name']]);

    visit(frontend('/api/client'))
        ->assertSee('"name":"tree"');

    expect(app('graph')['self']['name'])->toBe('tree');
});
