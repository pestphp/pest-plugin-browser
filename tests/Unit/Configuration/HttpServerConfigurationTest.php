<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Contracts\HttpServer;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\ComputeUrl;

$resetServerManager = function (): void {
    new ReflectionProperty(ServerManager::class, 'instance')->setValue(null, null);
};

beforeEach($resetServerManager);
afterEach($resetServerManager);

it('uses the configured HTTP server factory', function (): void {
    $server = new ExampleHttpServer;
    $factoryCalls = 0;

    $configuration = (new Configuration)->httpServer(function () use (&$factoryCalls, $server): HttpServer {
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
