<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\DetachedProcess;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Frontend\Network;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
    $this->slowModule = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-slow-bridge-'.$this->bridge->port.'.cjs';
    $this->marker = $this->slowModule.'.loaded';
    $this->nodeOptions = getenv('NODE_OPTIONS');

    @unlink($this->marker);
});

afterEach(function (): void {
    $this->nodeOptions === false ? putenv('NODE_OPTIONS') : putenv('NODE_OPTIONS='.$this->nodeOptions);
    @unlink($this->slowModule);
    @unlink($this->marker);

    FrontendFixture::stopBridge($this->bridge);
});

function slowBridgeModule(string $module, string $marker, bool $ignoreTerm): void
{
    file_put_contents($module, implode("\n", [
        'require("fs").writeFileSync('.json_encode($marker).', String(process.pid));',
        $ignoreTerm ? 'process.on("SIGTERM", () => {});' : '',
        'Atomics.wait(new Int32Array(new SharedArrayBuffer(4)), 0, 0, 20000);',
    ]));

    putenv('NODE_OPTIONS=--require "'.$module.'"');
}

it('stops the bridge it started when the bridge does not start in time', function (): void {
    slowBridgeModule($this->slowModule, $this->marker, ignoreTerm: false);

    expect(fn () => $this->bridge->ensureRunning())->toThrow(FrontendUnavailableException::class)
        ->and($this->marker)->toBeFile();

    putenv('NODE_OPTIONS');

    expect(Network::waitUntil(fn (): bool => Network::isListening($this->bridge->port, 0.2), 8))->toBeFalse();
});

it('kills the bridge it started when the bridge ignores the request to stop', function (): void {
    slowBridgeModule($this->slowModule, $this->marker, ignoreTerm: true);

    expect(fn () => $this->bridge->ensureRunning())->toThrow(FrontendUnavailableException::class)
        ->and($this->marker)->toBeFile();

    $pid = (int) file_get_contents($this->marker);

    expect(Network::waitUntil(fn (): bool => ! DetachedProcess::isRunning($pid), 2))->toBeTrue();
});
