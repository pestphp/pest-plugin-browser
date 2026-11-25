<?php

declare(strict_types=1);

use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, true, Frontend::project());
    $this->bridge->ensureRunning();
    $this->bridge->register(getmypid(), FrontendFixture::backend());
});

afterEach(function (): void {
    FrontendFixture::stopBridge($this->bridge);
    FrontendFixture::stopBackends();
});

it('forwards the default port a route header names with the lane host', function (string $forwarded, string $host): void {
    $context = stream_context_create(['http' => ['header' => 'X-Forwarded-Host: '.sprintf($forwarded, getmypid()), 'ignore_errors' => true]]);

    $response = json_decode((string) file_get_contents($this->bridge->url().'/users', false, $context), true);

    expect($response['host'])->toBe(sprintf($host, getmypid()))
        ->and($response['laneHost'])->toBe(sprintf($host, getmypid()));
})->with([
    ['acme-w%d.localhost:80', 'acme-w%d.localhost:80'],
    ['acme-w%d.localhost', 'acme-w%d.localhost'],
    ['acme-w%d.localhost:3101', 'acme-w%d.localhost:3101'],
]);
