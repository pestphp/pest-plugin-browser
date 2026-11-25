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

it('ignores a lane pattern in the path of the referer', function (): void {
    $this->bridge->ensureRunning();

    $own = FrontendFixture::backend();
    $this->bridge->register(getmypid(), $own);

    $context = stream_context_create(['http' => [
        'header' => 'Referer: http://127.0.0.1:3101/products/shoe-w42/edit',
        'ignore_errors' => true,
    ]]);

    $body = (string) file_get_contents($this->bridge->url().'/users', false, $context);

    expect($body)->not->toContain('pest-bridge')
        ->and(json_decode($body, true)['host'] ?? null)->toBe(mb_substr($own, 7));
});
