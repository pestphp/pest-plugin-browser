<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\DevServer;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Frontend\Network;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
    $this->port = Port::find();
    $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-broken-frontend-'.getmypid();

    @mkdir($this->directory);
    file_put_contents($this->directory.DIRECTORY_SEPARATOR.'router.php', '<?php http_response_code(500); echo "compile error";');
});

afterEach(function (): void {
    FrontendFixture::stopPort($this->port);
    FrontendFixture::stopBridge($this->bridge);

    @unlink($this->directory.DIRECTORY_SEPARATOR.'router.php');
    @rmdir($this->directory);
});

it('stops a dev server that does not become ready', function (): void {
    $devServer = new DevServer($this->directory, FrontendFixture::command(), 'localhost', $this->port, [], '/', 2, null);

    expect(fn () => $devServer->ensureRunning($this->bridge, FrontendFixture::laneHost()))->toThrow(FrontendUnavailableException::class, 'did not answer');

    expect(Network::waitUntil(fn (): bool => ! Network::isListening($this->port, 0.2), 2))->toBeTrue();
});
