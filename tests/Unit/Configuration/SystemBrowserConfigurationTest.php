<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    // Reset Playwright state before each test
    Playwright::setChannel(null);
    Playwright::setExecutablePath(null);
});

it('can set a system browser channel via configuration', function (): void {
    $config = new Configuration();

    $result = $config->usingChannel('chrome');

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::channel())->toBe('chrome');
});

it('can set a browser executable path via configuration', function (): void {
    $config = new Configuration();

    $result = $config->usingExecutablePath('/usr/bin/firefox');

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::executablePath())->toBe('/usr/bin/firefox');
});

it('defaults channel and executable path to null', function (): void {
    expect(Playwright::channel())->toBeNull();
    expect(Playwright::executablePath())->toBeNull();
});

it('supports fluent chaining with other configuration options', function (): void {
    $config = new Configuration();

    $result = $config
        ->usingChannel('msedge')
        ->usingExecutablePath('/usr/bin/msedge')
        ->headed()
        ->timeout(10000);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::channel())->toBe('msedge');
    expect(Playwright::executablePath())->toBe('/usr/bin/msedge');
});

it('can override channel and executable path multiple times', function (): void {
    Playwright::setChannel('chrome');
    expect(Playwright::channel())->toBe('chrome');

    Playwright::setChannel('msedge');
    expect(Playwright::channel())->toBe('msedge');

    Playwright::setExecutablePath('/usr/bin/firefox');
    expect(Playwright::executablePath())->toBe('/usr/bin/firefox');

    Playwright::setExecutablePath(null);
    expect(Playwright::executablePath())->toBeNull();
});
