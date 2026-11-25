<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\DevServer;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Frontend\FrontendConfiguration;
use Pest\Browser\Frontend\Network;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
    $this->port = Port::find();
});

afterEach(function (): void {
    Frontend::reset();
    FrontendFixture::stopPort($this->port);
    FrontendFixture::stopBridge($this->bridge);
});

function fixtureDevServer(int $port, Bridge $bridge, string $command = '', ?string $bridgePrefix = null, int $timeout = 10): DevServer
{
    return new DevServer(
        FrontendFixture::directory(),
        $command === '' ? FrontendFixture::command() : $command,
        'localhost',
        $port,
        ['FIXTURE_BACKEND_URL' => $bridge->url()],
        '/',
        $timeout,
        $bridgePrefix,
    );
}

it('configures the frontend from the browser configuration', function (): void {
    $configuration = pest()->browser()
        ->frontend(path: '../frontend', command: 'pnpm exec next dev -p {port}', port: 3101)
        ->backendUrlEnv('NEXT_BACKEND_URL', 'NEXT_TENANT_URL')
        ->env(['NEXTAUTH_URL' => '{url}', 'API' => '{bridge}/api'])
        ->bridgePort(9898)
        ->routeBy('X-Tenant-Domain', 'Referer')
        ->forwardLaneHost()
        ->subdomain('acme')
        ->readyPath('api/auth/csrf')
        ->timeout(30)
        ->verifyBridgeThrough('/hermes/api');

    $frontend = Frontend::instance();
    $devServer = $frontend->devServer();

    expect($configuration)->toBeInstanceOf(FrontendConfiguration::class)
        ->and($frontend->bridge())->toEqual(new Bridge(9898, ['x-tenant-domain', 'referer'], true, Frontend::project()))
        ->and($frontend->laneHost())->toBe('acme-w'.getmypid().'.localhost:3101')
        ->and($frontend->laneHost('tenant'))->toBe('tenant-w'.getmypid().'.localhost:3101')
        ->and($devServer->resolvedCommand())->toBe('pnpm exec next dev -p 3101')
        ->and($devServer->environment)->toBe([
            'NEXT_BACKEND_URL' => 'http://127.0.0.1:9898',
            'NEXT_TENANT_URL' => 'http://127.0.0.1:9898',
            'NEXTAUTH_URL' => 'http://localhost:3101',
            'API' => 'http://127.0.0.1:9898/api',
        ])
        ->and($devServer->readyPath)->toBe('/api/auth/csrf')
        ->and($devServer->timeout)->toBe(30)
        ->and($devServer->bridgePrefix)->toBe('/hermes/api')
        ->and($devServer->directory)->toEndWith(DIRECTORY_SEPARATOR.'frontend');
});

it('keeps the lane out of a host that is not localhost', function (): void {
    pest()->browser()->frontend('.', 'true', 3101, '127.0.0.1');

    expect(Frontend::instance()->laneHost())->toBe('127.0.0.1:3101');
});

it('requires a configured frontend', function (): void {
    expect(Frontend::configured())->toBeFalse()
        ->and(fn (): Frontend => Frontend::instance())->toThrow(LogicException::class, 'pest()->browser()->frontend(');
});

it('starts the dev server detached and waits until it answers', function (): void {
    $this->bridge->ensureRunning();

    fixtureDevServer($this->port, $this->bridge)->ensureRunning($this->bridge, FrontendFixture::laneHost());

    expect(Network::get('http://127.0.0.1:'.$this->port.'/environment', 2))
        ->body->json()->backend->toBe($this->bridge->url());
});

it('reuses the dev server that already listens', function (): void {
    $this->bridge->ensureRunning();
    fixtureDevServer($this->port, $this->bridge)->ensureRunning($this->bridge, FrontendFixture::laneHost());
    $pid = FrontendFixture::pidOf('http://127.0.0.1:'.$this->port);

    fixtureDevServer($this->port, $this->bridge)->ensureRunning($this->bridge, FrontendFixture::laneHost());

    expect(FrontendFixture::pidOf('http://127.0.0.1:'.$this->port))->toBe($pid);
});

it('verifies that the dev server forwards to this bridge', function (): void {
    $this->bridge->ensureRunning();

    fixtureDevServer($this->port, $this->bridge, bridgePrefix: '/api/')->ensureRunning($this->bridge, FrontendFixture::laneHost());
})->throwsNoExceptions();

it('refuses a dev server that forwards to another bridge', function (): void {
    $this->bridge->ensureRunning();
    $other = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());

    expect(fn () => fixtureDevServer($this->port, $this->bridge, bridgePrefix: '/api')->ensureRunning($other, FrontendFixture::laneHost()))
        ->toThrow(FrontendUnavailableException::class, 'does not forward [http://localhost:'.$this->port.'/api/__pest-bridge] to the browser bridge on port '.$other->port);
});

it('reports the log of a dev server that exits', function (): void {
    expect(fn () => fixtureDevServer($this->port, $this->bridge, 'echo "next: command not found" && exit 127')->ensureRunning($this->bridge, FrontendFixture::laneHost()))
        ->toThrow(FrontendUnavailableException::class, 'next: command not found');
});

it('refuses a frontend directory that does not exist', function (): void {
    $devServer = new DevServer('/does/not/exist', 'true', 'localhost', $this->port, [], '/', 1, null);

    expect(fn () => $devServer->ensureRunning($this->bridge, FrontendFixture::laneHost()))
        ->toThrow(FrontendUnavailableException::class, 'The frontend directory [/does/not/exist] does not exist.');
});

it('remembers that the frontend cannot start', function (): void {
    pest()->browser()
        ->frontend(FrontendFixture::directory(), 'exit 1', $this->port)
        ->bridgePort($this->bridge->port)
        ->timeout(5);

    expect(fn (): string => Frontend::instance()->url('/'))->toThrow(FrontendUnavailableException::class);

    $started = microtime(true);

    expect(fn (): string => Frontend::instance()->url('/'))->toThrow(FrontendUnavailableException::class)
        ->and(microtime(true) - $started)->toBeLessThan(0.5);
});
