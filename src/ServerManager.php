<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Contracts\HttpServer;
use Pest\Browser\Contracts\PlaywrightServer;
use Pest\Browser\Drivers\Laravel\LaravelHttpServer;
use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;
use Pest\Browser\Playwright\Servers\PlaywrightNpxServer;
use Pest\Browser\Support\Port;
use Pest\Plugins\Parallel;
use React\EventLoop\Loop;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

/**
 * @internal
 */
final class ServerManager
{
    /**
     * The default host for the server.
     */
    public const string DEFAULT_HOST = '127.0.0.1';

    /**
     * The singleton instance of the server manager.
     */
    private static ?ServerManager $instance = null;

    /**
     * The Playwright server process.
     */
    private ?PlaywrightServer $playwright = null;

    /**
     * The HTTP server process.
     */
    private ?HttpServer $http = null;

    /**
     * Gets the singleton instance of the server manager.
     */
    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Returns the Playwright server process instance.
     */
    public function playwright(): PlaywrightServer
    {
        if (Parallel::isWorker()) {
            return AlreadyStartedPlaywrightServer::fromPersisted();
        }

        $port = Port::find();

        $this->playwright ??= PlaywrightNpxServer::create(
            __DIR__.'/..',
            'npx playwright run-server --host %s --port %d',
            self::DEFAULT_HOST,
            $port,
            'Listening on',
        );

        AlreadyStartedPlaywrightServer::persist(
            self::DEFAULT_HOST,
            $port,
        );

        return $this->playwright;
    }

    /**
     * Returns the HTTP server process instance.
     */
    public function http(): HttpServer
    {
        return $this->http ??= new LaravelHttpServer(
            Loop::get(),
            new HttpFoundationFactory(),
            self::DEFAULT_HOST,
            Port::find(),
        );
    }
}
