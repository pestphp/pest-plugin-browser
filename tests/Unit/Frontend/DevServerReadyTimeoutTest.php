<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\DevServer;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
    $this->port = Port::find();
    $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-slow-frontend-'.getmypid();

    @mkdir($this->directory);
    file_put_contents($this->directory.DIRECTORY_SEPARATOR.'router.php', '<?php sleep(4); echo "frontend ready";');
});

afterEach(function (): void {
    FrontendFixture::stopPort($this->port);
    FrontendFixture::stopBridge($this->bridge);

    @unlink($this->directory.DIRECTORY_SEPARATOR.'router.php');
    @rmdir($this->directory);
});

it('waits no longer than the configured timeout for the dev server to answer', function (): void {
    $devServer = new DevServer($this->directory, FrontendFixture::command(), 'localhost', $this->port, [], '/', 1, null);

    $started = microtime(true);

    expect(fn () => $devServer->ensureRunning($this->bridge, FrontendFixture::laneHost()))
        ->toThrow(FrontendUnavailableException::class, 'did not answer');

    expect(microtime(true) - $started)->toBeGreaterThanOrEqual(1.0)->toBeLessThan(2.5);
});
