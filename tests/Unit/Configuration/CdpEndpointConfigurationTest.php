<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\ServerManager;

beforeEach(function (): void {
    Playwright::setCdpEndpoint(null);
    putenv('PEST_BROWSER_CDP_ENDPOINT');
});

afterEach(function (): void {
    Playwright::setCdpEndpoint(null);
    putenv('PEST_BROWSER_CDP_ENDPOINT');
});

it('launches a local browser by default', function (): void {
    expect(Playwright::cdpEndpoint())->toBeNull()
        ->and(Playwright::usesCdpEndpoint())->toBeFalse()
        ->and(ServerManager::playwrightCommand())->toContain('playwright run-server')
        ->and(ServerManager::playwrightCommand())->not->toContain('cdp-server.js');
});

it('can set a cdp endpoint via configuration', function (): void {
    $result = new Configuration()->connectOverCdp('ws://127.0.0.1:9222');

    expect($result)->toBeInstanceOf(Configuration::class)
        ->and(Playwright::cdpEndpoint())->toBe('ws://127.0.0.1:9222')
        ->and(Playwright::usesCdpEndpoint())->toBeTrue();
});

it('can unset the cdp endpoint via configuration', function (): void {
    new Configuration()->connectOverCdp('ws://127.0.0.1:9222')->connectOverCdp(null);

    expect(Playwright::cdpEndpoint())->toBeNull();
});

it('reads the cdp endpoint from the environment', function (): void {
    putenv('PEST_BROWSER_CDP_ENDPOINT=http://127.0.0.1:9222');

    expect(Playwright::cdpEndpoint())->toBe('http://127.0.0.1:9222');
});

it('ignores an empty cdp endpoint in the environment', function (): void {
    putenv('PEST_BROWSER_CDP_ENDPOINT=');

    expect(Playwright::cdpEndpoint())->toBeNull();
});

it('prefers the configured cdp endpoint over the environment', function (): void {
    putenv('PEST_BROWSER_CDP_ENDPOINT=ws://env:9222');
    Playwright::setCdpEndpoint('ws://config:9222');

    expect(Playwright::cdpEndpoint())->toBe('ws://config:9222');
});

it('starts the cdp server script when a cdp endpoint is configured', function (): void {
    Playwright::setCdpEndpoint('ws://127.0.0.1:9222');

    $command = ServerManager::playwrightCommand();

    expect($command)->toStartWith('node ')
        ->and($command)->toContain('cdp-server.js')
        ->and($command)->toContain('--endpoint')
        ->and($command)->toContain('ws://127.0.0.1:9222')
        ->and($command)->toContain('--host %s --port %d')
        ->and(sprintf($command, '127.0.0.1', 8000))->toContain('--host 127.0.0.1 --port 8000');
});

it('ships the cdp server script', function (): void {
    expect(file_exists(dirname(__DIR__, 3).'/resources/js/cdp-server.js'))->toBeTrue();
});

it('always uses chromium when attaching over cdp', function (): void {
    Playwright::setCdpEndpoint('ws://127.0.0.1:9222');

    expect(Playwright::usesCdpEndpoint())->toBeTrue()
        ->and(BrowserType::FIREFOX->toPlaywrightName())->toBe('firefox');
});
