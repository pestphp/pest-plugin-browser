<?php

declare(strict_types=1);

use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
});

afterEach(function (): void {
    FrontendFixture::stopBridge($this->bridge);
    FrontendFixture::stopBackends();
});

it('forwards a request whose query ends with the identity path', function (): void {
    $this->bridge->ensureRunning();

    $own = FrontendFixture::backend();
    $this->bridge->register(getmypid(), $own);

    $body = (string) file_get_contents($this->bridge->url().'/search?path='.Bridge::IDENTITY_PATH, false, stream_context_create(['http' => ['ignore_errors' => true]]));

    expect(json_decode($body, true))->toBe([
        'path' => '/search?path='.Bridge::IDENTITY_PATH,
        'host' => mb_substr($own, 7),
        'bridge' => '1',
        'laneHost' => null,
    ]);
});
