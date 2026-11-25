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

it('routes to the lane that Pest appends after a subdomain that ends with a lane pattern', function (): void {
    $this->bridge->ensureRunning();

    $own = FrontendFixture::backend();
    $other = FrontendFixture::backend();
    $this->bridge->register(getmypid(), $own);
    $this->bridge->register(FrontendFixture::pidOf($other), $other);

    $host = sprintf('acme-w%d.shop-w%d.localhost:3101', FrontendFixture::pidOf($other), getmypid());

    $context = stream_context_create(['http' => [
        'header' => "X-Forwarded-Host: {$host}",
        'ignore_errors' => true,
    ]]);

    $body = (string) file_get_contents($this->bridge->url().'/users', false, $context);

    expect(json_decode($body, true)['host'] ?? null)->toBe(mb_substr($own, 7));
});

it('routes to the lane that Pest appends to an internationalized subdomain', function (string $subdomain): void {
    $this->bridge->ensureRunning();

    $own = FrontendFixture::backend();
    $other = FrontendFixture::backend();
    $this->bridge->register(getmypid(), $own);
    $this->bridge->register(FrontendFixture::pidOf($other), $other);

    $host = sprintf('%s-w%d.localhost:3101', $subdomain, getmypid());

    $context = stream_context_create(['http' => [
        'header' => "X-Forwarded-Host: {$host}",
        'ignore_errors' => true,
    ]]);

    $body = (string) file_get_contents($this->bridge->url().'/users', false, $context);

    expect(json_decode($body, true)['host'] ?? null)->toBe(mb_substr($own, 7));
})->with(['caf%C3%A9', 'xn--caf-dma', 'xn--strae-oqa']);
