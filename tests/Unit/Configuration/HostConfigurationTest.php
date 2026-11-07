<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Plugin;
use Pest\Browser\ServerManager;

beforeEach(function (): void {
    // Reset Playwright state before each test
    Playwright::setHost(null);
});

afterEach(function (): void {
    Playwright::setHost(null);
});

it('can set host via the CLI', function (array $options): void {
    Playwright::setHost('previous.localhost');

    $arguments = (new Plugin)->handleArguments([
        'tests/Browser/ExampleTest.php',
        ...$options,
        '--ci',
    ]);

    expect(Playwright::host())->toBe('host.docker.internal')
        ->and($arguments)->toBe(['tests/Browser/ExampleTest.php', '--ci']);
})->with([
    'separate value' => [['--host', 'host.docker.internal']],
    'equals value' => [['--host=host.docker.internal']],
]);

it('requires a value for the host CLI option', function (array $arguments): void {
    expect(fn (): array => (new Plugin)->handleArguments($arguments))
        ->toThrow(InvalidArgumentException::class, 'The "--host" argument requires a value.')
        ->and(Playwright::host())->toBeNull();
})->with([
    'missing value' => [['--host']],
    'empty value' => [['--host=']],
    'another option' => [['--host', '--ci']],
]);

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

it('does not create the http server when setting a host', function (): void {
    $hadServer = ServerManager::instance()->hasHttp();

    new Configuration()->withHost('tenant.localhost');

    expect(ServerManager::instance()->hasHttp())->toBe($hadServer);
});
