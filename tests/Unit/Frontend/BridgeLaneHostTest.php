<?php

declare(strict_types=1);

use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
    $this->bridge->ensureRunning();
    $this->bridge->register(getmypid(), FrontendFixture::backend());
});

afterEach(function (): void {
    FrontendFixture::stopBridge($this->bridge);
    FrontendFixture::stopBackends();
});

function bridgeLaneHostRequest(Bridge $bridge, string $headers): array
{
    $context = stream_context_create(['http' => ['header' => $headers, 'ignore_errors' => true]]);

    return json_decode((string) file_get_contents($bridge->url().'/users', false, $context), true);
}

it('tells the backend the lane host it routed a request by', function (): void {
    $host = sprintf('acme-w%d.localhost:3101', getmypid());

    $response = bridgeLaneHostRequest($this->bridge, "X-Forwarded-Host: {$host}\r\nX-Pest-Lane-Host: other-w1.localhost:3102");

    expect($response['laneHost'])->toBe($host);
});

it('drops the lane host a client sends when the request has no lane host to route by', function (): void {
    $response = bridgeLaneHostRequest($this->bridge, 'X-Pest-Lane-Host: other-w1.localhost:3102');

    expect($response['laneHost'])->toBeNull();
});
