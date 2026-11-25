<?php

declare(strict_types=1);

use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, true, Frontend::project());
});

afterEach(function (): void {
    FrontendFixture::stopBridge($this->bridge);
    FrontendFixture::stopBackends();
});

it('forwards the host of the lane when an explicit lane header names the same process', function (): void {
    $this->bridge->ensureRunning();
    $this->bridge->register(getmypid(), FrontendFixture::backend());

    $host = sprintf('acme-w%d.localhost:3101', getmypid());

    $context = stream_context_create(['http' => [
        'header' => implode("\r\n", ['X-Pest-Lane: '.getmypid(), "X-Forwarded-Host: {$host}"]),
        'ignore_errors' => true,
    ]]);

    $body = (string) file_get_contents($this->bridge->url().'/users', false, $context);

    expect(json_decode($body, true)['host'] ?? null)->toBe($host);
});
