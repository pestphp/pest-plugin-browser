<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    // Reset Playwright state before each test
    Playwright::setChannel(null);
    Playwright::setExecutablePath(null);
});

it('builds launch options without null values', function (): void {
    $options = Client::launchOptions();

    expect($options)->toHaveKeys(['headless', 'ignoreHTTPSErrors', 'bypassCSP']);
    expect($options)->not->toHaveKeys(['channel', 'executablePath']);
});

it('keeps headless disabled when running headed', function (): void {
    Playwright::headed();

    expect(Client::launchOptions()['headless'])->toBeFalse();
});

it('includes the configured channel in the launch options', function (): void {
    Playwright::setChannel('chrome');

    expect(Client::launchOptions()['channel'])->toBe('chrome');
});

it('includes the configured executable path in the launch options', function (): void {
    Playwright::setExecutablePath('/usr/bin/google-chrome');

    expect(Client::launchOptions()['executablePath'])->toBe('/usr/bin/google-chrome');
});

it('builds a connection query that round-trips the launch options', function (): void {
    $launchOptions = [
        'headless' => false,
        'executablePath' => '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    ];

    $query = Client::connectionQuery('chromium', $launchOptions);

    expect($query)->toContain('browser=chromium');
    expect($query)->toContain('launch-options='.urlencode((string) json_encode($launchOptions)));
});
