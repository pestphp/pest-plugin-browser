<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Contracts\HttpServer;
use Pest\Browser\Contracts\PlaywrightServer;
use Pest\Browser\Drivers\LaravelHttpServer;
use Pest\Browser\Drivers\NullableHttpServer;
use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;
use Pest\Browser\Playwright\Servers\PlaywrightNpmServer;
use Pest\Browser\Support\PackageJsonDirectory;
use Pest\Browser\Support\Port;
use Pest\Plugins\Parallel;

/**
 * @internal
 *
 * @codeCoverageIgnore
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

        if (! $this->playwright instanceof PlaywrightServer) {
            $port = Port::find();

            $this->playwright = PlaywrightNpmServer::create(
                PackageJsonDirectory::find(),
                '.'.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'.bin'.DIRECTORY_SEPARATOR.'playwright run-server --host %s --port %d --mode launchServer',
                self::DEFAULT_HOST,
                $port,
                'Listening on',
            );

            AlreadyStartedPlaywrightServer::persist(
                self::DEFAULT_HOST,
                $port,
            );
        }

        return $this->playwright;
    }

    /**
     * Returns the HTTP server process instance.
     */
    public function http(): HttpServer
    {
        return $this->http ??= match (function_exists('app_path')) {
            true => new LaravelHttpServer(
                self::DEFAULT_HOST,
                Port::find(),
            ),
            default => new NullableHttpServer(),
        };
    }
}
