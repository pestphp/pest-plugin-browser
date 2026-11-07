<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;
use Pest\Browser\Playwright\Servers\PlaywrightNpmServer;
use Pest\Browser\Playwright\Servers\RemotePlaywrightServer;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\Port;
use Pest\Plugins\Parallel;
use Symfony\Component\Process\Process;

beforeEach(function (): void {
    new ReflectionProperty(ServerManager::class, 'instance')->setValue(null, null);
    putenv('PEST_BROWSER_STATE_FILE='.sys_get_temp_dir().'/pest-browser-remote-'.uniqid().'.json');
});

afterEach(function (): void {
    AlreadyStartedPlaywrightServer::markAsStopped();
    putenv('PEST_BROWSER_STATE_FILE');
    new ReflectionProperty(ServerManager::class, 'instance')->setValue(null, null);
});

it('uses a remote endpoint without creating a local process', function (string $endpoint): void {
    $configuration = (new Configuration)->useRemoteConnection($endpoint);
    $server = ServerManager::instance()->playwright();

    expect($configuration)->toBeInstanceOf(Configuration::class)
        ->and($server)->toBeInstanceOf(RemotePlaywrightServer::class)
        ->and($server->url())->toBe($endpoint)
        ->and(ServerManager::instance()->playwright())->toBe($server);

    $server->start();

    expect(AlreadyStartedPlaywrightServer::fromPersisted()->url())->toBe($endpoint);

    $server->stop();

    expect($server->url())->toBe($endpoint)
        ->and(file_exists((string) getenv('PEST_BROWSER_STATE_FILE')))->toBeFalse();
})->with([
    'ws://127.0.0.1:3000',
    'wss://playwright.example.test/session/path?token=abc%2B123',
]);

it('continues to use a local server by default', function (): void {
    if (Parallel::isWorker()) {
        AlreadyStartedPlaywrightServer::persist('127.0.0.1', 4001);

        expect(ServerManager::instance()->playwright())
            ->toBeInstanceOf(AlreadyStartedPlaywrightServer::class)
            ->url()->toBe('127.0.0.1:4001');

        return;
    }

    $server = ServerManager::instance()->playwright();

    expect($server)->toBeInstanceOf(PlaywrightNpmServer::class)
        ->and(ServerManager::instance()->playwright())->toBe($server);
});

it('rejects invalid remote endpoints', function (string $endpoint): void {
    expect(fn (): Configuration => (new Configuration)->useRemoteConnection($endpoint))
        ->toThrow(InvalidArgumentException::class);
})->with(['', 'localhost:3000', 'https://example.test', 'ws:///missing-host', 'ws://example.test/#fragment']);

it('does not allow changing the endpoint after the server was created', function (): void {
    (new Configuration)->useRemoteConnection('ws://example.test:3000');

    expect(fn (): Configuration => (new Configuration)->useRemoteConnection('ws://other.example.test:3000'))
        ->toThrow(LogicException::class);
});

it('runs browser commands remotely without stopping the external server', function (): void {
    $port = Port::find();
    $address = '127.0.0.1:'.$port;
    $process = new Process([
        './node_modules/.bin/playwright', 'run-server', '--host', '127.0.0.1',
        '--port', (string) $port, '--mode', 'launchServer', '--path', '/session',
    ], dirname(__DIR__, 3));
    $process->setTimeout(30);

    try {
        $process->start();

        if (! $process->waitUntil(fn (string $type, string $output): bool => str_contains($output, 'Listening on'))) {
            throw new RuntimeException('Could not start the remote Playwright fixture: '.$process->getErrorOutput());
        }

        (new Configuration)->useRemoteConnection('ws://'.$address.'/session?token=example');
        $server = ServerManager::instance()->playwright();
        $server->start();

        Client::instance()->connectTo(AlreadyStartedPlaywrightServer::fromPersisted()->url());
        $page = Playwright::browser(BrowserType::CHROME)->launch()->newContext()->newPage();
        $page->goto('data:text/html,<title>Remote Playwright</title>');

        expect($page->title())->toBe('Remote Playwright');

        $server->stop();

        expect($process->isRunning())->toBeTrue()
            ->and($page->title())->toBe('Remote Playwright');
    } finally {
        try {
            Playwright::close();
            Client::instance()->disconnect();
        } finally {
            $process->stop();
        }
    }
});
