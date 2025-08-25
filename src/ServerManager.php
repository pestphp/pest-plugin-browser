<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Contracts\HttpServer;
use Pest\Browser\Contracts\PlaywrightServer;
use Pest\Browser\Drivers\LaravelHttpServer;
use Pest\Browser\Drivers\NullableHttpServer;
use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;
use Pest\Browser\Playwright\Servers\ExistingPlaywrightServer;
use Pest\Browser\Playwright\Servers\PlaywrightNpmServer;
use Pest\Browser\Support\PackageJsonDirectory;
use Pest\Browser\Support\PersistHttpServer;
use Pest\Browser\Support\PersistPlaywrightServer;
use Pest\Browser\Support\Port;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class ServerManager
{
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
        return self::$instance ??= self::newInstance();
    }

    /**
     * Creates a new instance of the server manager.
     */
    public static function newInstance(): self
    {
        return new self;
    }

    /**
     * Returns the Playwright server process instance.
     */
    public function playwright(): PlaywrightServer
    {
        if (PersistPlaywrightServer::wantsAlreadyStartedPlaywrightServer()) {
            return AlreadyStartedPlaywrightServer::fromPersisted();
        }

        if (PersistPlaywrightServer::wantsExistingPlaywrightServer()) {
            return ExistingPlaywrightServer::fromExisting()->persistSelf();
        }

        $host = PersistPlaywrightServer::host();
        $port = Port::find(PersistPlaywrightServer::port());

        $this->playwright ??= PlaywrightNpmServer::create(
            PackageJsonDirectory::find(),
            '.'.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'.bin'.DIRECTORY_SEPARATOR.'playwright run-server --host %s --port %d --mode launchServer',
            $host,
            $port,
            'Listening on',
        );

        AlreadyStartedPlaywrightServer::persist(
            $host,
            $port,
        );

        return $this->playwright;
    }

    /**
     * Returns the HTTP server process instance.
     */
    public function http(): HttpServer
    {
        $runningLaravel = function_exists('app_path');
        if (! $runningLaravel) {
            return $this->http ??= new NullableHttpServer();
        }
        $port = Port::find(PersistHttpServer::port());
        if (PersistHttpServer::wantsPersisted()) {
            $this->http ??= LaravelHttpServer::fromPersisted($port);

            return $this->http;
        }
        $this->http ??= new LaravelHttpServer(
            PersistHttpServer::host(),
            $port,
            PersistHttpServer::bindAddress()
        );

        return $this->http;
    }
}
