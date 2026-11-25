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
    $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-large-frontend-'.getmypid();

    @mkdir($this->directory);
    file_put_contents($this->directory.DIRECTORY_SEPARATOR.'router.php', '<?php echo str_repeat("a", 11 * 1024 * 1024);');
});

afterEach(function (): void {
    FrontendFixture::stopPort($this->port);
    FrontendFixture::stopBridge($this->bridge);

    @unlink($this->directory.DIRECTORY_SEPARATOR.'router.php');
    @rmdir($this->directory);
});

it('takes a dev server whose ready page is larger than the response limit of the HTTP client as ready', function (): void {
    $devServer = new DevServer($this->directory, FrontendFixture::command(), 'localhost', $this->port, [], '/', 5, null);

    $devServer->ensureRunning($this->bridge, FrontendFixture::laneHost());

    expect(FrontendFixture::pidOf('http://127.0.0.1:'.$this->port))->toBeGreaterThan(0);
});
