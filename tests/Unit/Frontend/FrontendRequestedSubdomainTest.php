<?php

declare(strict_types=1);

use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
    $this->port = Port::find();
    $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-tenant-frontend-'.getmypid();

    @mkdir($this->directory);
    file_put_contents($this->directory.DIRECTORY_SEPARATOR.'router.php', '<?php if (! str_starts_with((string) $_SERVER["HTTP_HOST"], "acme-")) { http_response_code(500); } echo "tenant";');
});

afterEach(function (): void {
    Frontend::reset();
    FrontendFixture::stopPort($this->port);
    FrontendFixture::stopBridge($this->bridge);

    @unlink($this->directory.DIRECTORY_SEPARATOR.'router.php');
    @rmdir($this->directory);
});

it('checks the readiness of the frontend with the requested subdomain', function (): void {
    pest()->browser()
        ->frontend($this->directory, FrontendFixture::command(), $this->port)
        ->bridgePort($this->bridge->port)
        ->timeout(3);

    expect(Frontend::instance()->url('/', 'acme'))->toBe('http://acme-w'.getmypid().'.localhost:'.$this->port.'/');
});
