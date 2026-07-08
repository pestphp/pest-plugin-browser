<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Support\Screenshot;

function resetConfigurationScreenshotDir(): void
{
    $property = new ReflectionProperty(Screenshot::class, 'dir');
    $property->setValue(null, null);
}

beforeEach(function (): void {
    // Reset Playwright state before each test
    Playwright::setHost(null);
    resetConfigurationScreenshotDir();
});

afterEach(function (): void {
    resetConfigurationScreenshotDir();
});

it('can set host via configuration', function (): void {
    $config = new Configuration();

    $result = $config->withHost('tenant.localhost');

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::host())->toBe('tenant.localhost');
});

it('follows fluent interface pattern', function (): void {
    $config = new Configuration();

    $result = $config
        ->withHost('app.localhost')
        ->userAgent('Test Agent')
        ->timeout(10000);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::host())->toBe('app.localhost');
});

it('can set screenshots directory via configuration', function (): void {
    $config = new Configuration();
    $dir = sys_get_temp_dir().'/pest-browser-configuration-'.uniqid('', true);

    $result = $config->screenshots($dir);

    expect($result)->toBeInstanceOf(Configuration::class)
        ->and(Screenshot::dir())->toBe($dir);
});

it('stores host in Playwright global state', function (): void {
    expect(Playwright::host())->toBeNull();

    Playwright::setHost('custom.localhost');

    expect(Playwright::host())->toBe('custom.localhost');
});

it('can override host multiple times', function (): void {
    Playwright::setHost('first.localhost');
    expect(Playwright::host())->toBe('first.localhost');

    Playwright::setHost('second.localhost');
    expect(Playwright::host())->toBe('second.localhost');

    Playwright::setHost('final.localhost');
    expect(Playwright::host())->toBe('final.localhost');
});

it('handles various host formats', function (): void {
    $hosts = [
        'localhost',
        'app.localhost',
        'subdomain.domain.tld',
        '192.168.1.100',
        'custom-host.test',
    ];

    foreach ($hosts as $host) {
        Playwright::setHost($host);
        expect(Playwright::host())->toBe($host);
    }
});
