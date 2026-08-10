<?php

declare(strict_types=1);

use InvalidArgumentException;
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

    $result = $config->usingExecutablePath('/usr/bin/google-chrome');

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::executablePath())->toBe('/usr/bin/google-chrome');
});

it('defaults channel and executable path to null', function (): void {
    expect(Playwright::channel())->toBeNull();
    expect(Playwright::executablePath())->toBeNull();
});

it('supports fluent chaining with other configuration options', function (): void {
    $config = new Configuration();

    $result = $config
        ->usingChannel('msedge')
        ->headed()
        ->timeout(10000);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::channel())->toBe('msedge');
});

it('supports fluent chaining of an executable path with other options', function (): void {
    $config = new Configuration();

    $result = $config
        ->usingExecutablePath('/usr/bin/google-chrome')
        ->headed()
        ->timeout(10000);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::executablePath())->toBe('/usr/bin/google-chrome');
});

it('throws when combining a channel with an executable path', function (): void {
    $config = new Configuration();

    $config->usingChannel('chrome');

    $config->usingExecutablePath('/usr/bin/google-chrome');
})->throws(InvalidArgumentException::class);

it('throws when combining an executable path with a channel', function (): void {
    $config = new Configuration();

    $config->usingExecutablePath('/usr/bin/google-chrome');

    $config->usingChannel('chrome');
})->throws(InvalidArgumentException::class);

it('can override channel and executable path multiple times', function (): void {
    Playwright::setChannel('chrome');
    expect(Playwright::channel())->toBe('chrome');

    Playwright::setChannel('msedge');
    expect(Playwright::channel())->toBe('msedge');

    Playwright::setExecutablePath('/usr/bin/google-chrome');
    expect(Playwright::executablePath())->toBe('/usr/bin/google-chrome');

    Playwright::setExecutablePath(null);
    expect(Playwright::executablePath())->toBeNull();
});
