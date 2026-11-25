<?php

declare(strict_types=1);

use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\DevServer;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Frontend\Network;
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

it('does not stop the dev server of another host that shares the port', function (): void {
    $ipv6Command = escapeshellarg(PHP_BINARY).' -S [::1]:{port} router.php';
    $ipv6Server = new DevServer(FrontendFixture::directory(), $ipv6Command, 'localhost', $this->port, [], '/', 3, null);
    $ipv4Server = new DevServer(FrontendFixture::directory(), FrontendFixture::command(), '127.0.0.1', $this->port, [], '/', 3, null);

    $ipv6Server->ensureRunning($this->bridge, FrontendFixture::laneHost());
    $ipv4Server->ensureRunning($this->bridge, '127.0.0.1:'.$this->port);

    $ipv6Server->ensureRunning($this->bridge, FrontendFixture::laneHost());

    expect(Network::isListening($this->port, 0.2, ['127.0.0.1']))->toBeTrue();
});
