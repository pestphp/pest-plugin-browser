<?php

declare(strict_types=1);

use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\DevServer;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
    $this->port = Port::find();
});

afterEach(function (): void {
    FrontendFixture::stopPort($this->port);
    FrontendFixture::stopBridge($this->bridge);
});

it('starts a dev server configured on the bracketed IPv6 loopback host', function (): void {
    $command = escapeshellarg(PHP_BINARY).' -S {host}:{port} router.php';
    $devServer = new DevServer(FrontendFixture::directory(), $command, '[::1]', $this->port, [], '/', 3, null);

    $devServer->ensureRunning($this->bridge, '[::1]:'.$this->port);

    expect(file_get_contents($devServer->url().'/'))->toBe('frontend ready');
});
