<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Contracts\HttpServer;
use Pest\Browser\Drivers\LaravelHttpServer;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Plugin;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\ComputeUrl;

$resetServerManager = function (): void {
    new ReflectionProperty(ServerManager::class, 'instance')->setValue(null, null);
    Playwright::setHost(null);
};

beforeEach($resetServerManager);
afterEach($resetServerManager);

it('binds the default HTTP server to loopback', function (): void {
    $server = ServerManager::instance()->http();

    expect($server)->toBeInstanceOf(LaravelHttpServer::class)
        ->and($server->host)->toBe('127.0.0.1');
});

it('can configure the HTTP server host without creating the server', function (): void {
    $configuration = (new Configuration)->withServerHost('0.0.0.0');

    expect($configuration)->toBeInstanceOf(Configuration::class)
        ->and(ServerManager::instance()->hasHttp())->toBeFalse()
        ->and(ServerManager::instance()->http()->host)->toBe('0.0.0.0');
});

it('can set the HTTP server host via the CLI independently of the browser host', function (array $options): void {
    $arguments = (new Plugin)->handleArguments([
        'tests/Browser/ExampleTest.php',
        ...$options,
        '--host=host.docker.internal',
        '--ci',
    ]);

    expect(ServerManager::instance()->http()->host)->toBe('0.0.0.0')
        ->and(Playwright::host())->toBe('host.docker.internal')
        ->and($arguments)->toBe(['tests/Browser/ExampleTest.php', '--ci']);
})->with([
    'separate value' => [['--server-host', '0.0.0.0']],
    'equals value' => [['--server-host=0.0.0.0']],
]);

it('requires a value for the HTTP server host CLI option', function (array $arguments): void {
    expect(fn (): array => (new Plugin)->handleArguments($arguments))
        ->toThrow(InvalidArgumentException::class, 'The "--server-host" argument requires a value.')
        ->and(ServerManager::instance()->hasHttp())->toBeFalse()
        ->and(ServerManager::instance()->http()->host)->toBe('127.0.0.1');
})->with([
    'missing value' => [['--server-host']],
    'empty value' => [['--server-host=']],
    'another option' => [['--server-host', '--ci']],
]);

it('does not allow changing the HTTP server host after it was created', function (): void {
    ServerManager::instance()->http();

    expect(fn (): Configuration => (new Configuration)->withServerHost('0.0.0.0'))
        ->toThrow(LogicException::class);
});

it('uses the configured HTTP server factory', function (): void {
    $server = new ExampleHttpServer;
    $factoryCalls = 0;

    $configuration = (new Configuration)->withServerHost('0.0.0.0')->httpServer(function () use (&$factoryCalls, $server): HttpServer {
        $factoryCalls++;

        return $server;
    });

    expect($configuration)->toBeInstanceOf(Configuration::class)
        ->and($factoryCalls)->toBe(0)
        ->and(ServerManager::instance()->http())->toBe($server)
        ->and(ComputeUrl::from('/example/path'))->toBe('http://example.test/example/path')
        ->and($factoryCalls)->toBe(1);
});

it('does not allow changing the HTTP server after it was created', function (): void {
    $manager = new ServerManager;
    $manager->setHttpServerFactory(static fn (): HttpServer => new ExampleHttpServer);
    $manager->http();

    expect(fn () => $manager->setHttpServerFactory(static fn (): HttpServer => new ExampleHttpServer))
        ->toThrow(LogicException::class);
});

final class ExampleHttpServer implements HttpServer
{
    public function start(): void {}

    public function stop(): void {}

    public function rewrite(string $url): string
    {
        return 'http://example.test'.$url;
    }

    public function flush(): void {}

    public function bootstrap(): void {}

    public function lastThrowable(): ?Throwable
    {
        return null;
    }

    public function throwLastThrowableIfNeeded(): void {}
}
