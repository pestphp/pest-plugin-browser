<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

use LogicException;
use Pest\Browser\Drivers\LaravelHttpServer;
use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\ServerManager;
use Pest\TestSuite;

/**
 * @internal
 */
final class Frontend
{
    /**
     * The default subdomain that carries the lane in the URL of the frontend.
     */
    public const string DEFAULT_SUBDOMAIN = 'pest';

    /**
     * The default headers in which the bridge looks for the lane.
     *
     * @var list<string>
     */
    public const array DEFAULT_ROUTE_HEADERS = ['x-forwarded-host', 'referer', 'origin'];

    /**
     * The names of the environment variables that receive the URL of the bridge.
     *
     * @var list<string>
     */
    public array $backendUrlEnv = [];

    /**
     * The extra environment variables of the frontend process.
     *
     * @var array<string, string>
     */
    public array $environment = [];

    /**
     * The port of the bridge, or null for the port of this project.
     */
    public ?int $bridgePort = null;

    /**
     * The headers in which the bridge looks for the lane.
     *
     * @var list<string>
     */
    public array $routeHeaders = self::DEFAULT_ROUTE_HEADERS;

    /**
     * Whether the bridge forwards the host of the lane to the backend.
     */
    public bool $forwardLaneHost = false;

    /**
     * The path that must answer before the frontend is ready.
     */
    public string $readyPath = '/';

    /**
     * The seconds the frontend has to become ready.
     */
    public int $timeout = 120;

    /**
     * The path prefix the frontend forwards to the backend, used to verify it forwards to this bridge.
     */
    public ?string $bridgePrefix = null;

    /**
     * The subdomain that carries the lane in the URL of the frontend.
     */
    public string $subdomain = self::DEFAULT_SUBDOMAIN;

    /**
     * The configured frontend, if any.
     */
    private static ?self $instance = null;

    /**
     * The reason the frontend is not available in this process, once known.
     */
    private ?string $unavailable = null;

    /**
     * Whether the bridge and the frontend run for this process.
     */
    private bool $prepared = false;

    /**
     * The lane this process published, if any.
     */
    private ?int $registeredLane = null;

    /**
     * Creates a new frontend instance.
     */
    public function __construct(
        public readonly string $path,
        public readonly string $command,
        public readonly int $port,
        public readonly string $host,
    ) {
        //
    }

    /**
     * Configures the frontend the browser tests drive.
     */
    public static function configure(string $path, string $command, int $port, string $host): self
    {
        self::$instance?->terminate();

        return self::$instance = new self($path, $command, $port, $host);
    }

    /**
     * Determines whether a frontend is configured.
     */
    public static function configured(): bool
    {
        return self::$instance instanceof self;
    }

    /**
     * Returns the configured frontend.
     */
    public static function instance(): self
    {
        if (! self::$instance instanceof self) {
            throw new LogicException('No frontend is configured. Configure it with [pest()->browser()->frontend(...)].');
        }

        return self::$instance;
    }

    /**
     * Forgets the configured frontend.
     */
    public static function reset(): void
    {
        self::$instance?->terminate();

        self::$instance = null;
    }

    /**
     * Returns the lane of this process.
     */
    public static function lane(): int
    {
        return (int) getmypid();
    }

    /**
     * Returns the key of the project under test, so that two projects or worktrees never share a bridge or a lane.
     */
    public static function project(): string
    {
        return mb_substr(hash('xxh128', TestSuite::getInstance()->rootPath), 0, 12);
    }

    /**
     * Returns the URL of the given path on the frontend, for the lane of this process.
     *
     * @throws FrontendUnavailableException
     */
    public function url(string $path = '/', ?string $subdomain = null): string
    {
        $this->prepare($subdomain);

        return sprintf('http://%s/%s', $this->laneHost($subdomain), mb_ltrim($path, '/'));
    }

    /**
     * Returns the host of the frontend for the lane of this process, such as "pest-w4123.localhost:3101".
     */
    public function laneHost(?string $subdomain = null): string
    {
        if ($this->host !== 'localhost') {
            return sprintf('%s:%d', Network::urlHost($this->host), $this->port);
        }

        return sprintf('%s-w%d.localhost:%d', $subdomain ?? $this->subdomain, self::lane(), $this->port);
    }

    /**
     * Returns the bridge between the frontend and the backends.
     */
    public function bridge(): Bridge
    {
        return new Bridge(
            $this->bridgePort ?? 10000 + crc32(self::project()) % 10000,
            array_values(array_unique(array_map(mb_strtolower(...), $this->routeHeaders))),
            $this->forwardLaneHost,
            self::project(),
        );
    }

    /**
     * Returns the dev server of the frontend.
     */
    public function devServer(): DevServer
    {
        $bridgeUrl = $this->bridge()->url();
        $frontendUrl = sprintf('http://%s:%d', Network::urlHost($this->host), $this->port);

        $placeholders = [
            '{bridge}' => $bridgeUrl,
            '{url}' => $frontendUrl,
            '{port}' => (string) $this->port,
        ];

        $environment = array_fill_keys($this->backendUrlEnv, $bridgeUrl);

        foreach ($this->environment as $name => $value) {
            $environment[$name] = strtr($value, $placeholders);
        }

        return new DevServer(
            $this->directory(),
            $this->command,
            $this->host,
            $this->port,
            $environment,
            '/'.mb_ltrim($this->readyPath, '/'),
            $this->timeout,
            $this->bridgePrefix,
        );
    }

    /**
     * Removes the lane of this process from the bridge.
     */
    public function terminate(): void
    {
        if ($this->registeredLane === null) {
            return;
        }

        $this->bridge()->forget($this->registeredLane);

        $this->registeredLane = null;
    }

    /**
     * Returns the absolute directory of the frontend.
     */
    public function directory(): string
    {
        $path = $this->path;

        if (! str_starts_with($path, DIRECTORY_SEPARATOR) && preg_match('/^[A-Za-z]:[\\\\\/]/', $path) !== 1) {
            $path = TestSuite::getInstance()->rootPath.DIRECTORY_SEPARATOR.$path;
        }

        $resolved = realpath($path);

        return $resolved === false ? $path : $resolved;
    }

    /**
     * Starts the bridge and the frontend once per process.
     *
     * @throws FrontendUnavailableException
     */
    private function prepare(?string $subdomain): void
    {
        if ($this->unavailable !== null) {
            throw new FrontendUnavailableException($this->unavailable);
        }

        if ($this->prepared) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $this->unavailable = 'Browser tests against a separate frontend do not run on Windows yet.';

            throw new FrontendUnavailableException($this->unavailable);
        }

        if ($this->bridge()->port === $this->port) {
            $this->unavailable = sprintf('The browser bridge and the frontend both use port %d. Choose another port with [->bridgePort()].', $this->port);

            throw new FrontendUnavailableException($this->unavailable);
        }

        $lock = fopen($this->devServer()->file('lock'), 'ce');

        try {
            if ($lock !== false) {
                flock($lock, LOCK_EX);
            }

            $http = ServerManager::instance()->http();

            if ($http instanceof LaravelHttpServer) {
                $http->probing($this->laneHost($subdomain));
            }

            try {
                $bridge = $this->bridge();

                $this->joinBridge($bridge);

                $this->devServer()->ensureRunning($bridge, $this->laneHost($subdomain));
            } finally {
                if ($http instanceof LaravelHttpServer) {
                    $http->probing(null);
                }
            }
        } catch (FrontendUnavailableException $exception) {
            $this->terminate();

            $this->unavailable = $exception->getMessage();

            throw $exception;
        } finally {
            if ($lock !== false) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }

        $this->prepared = true;
    }

    /**
     * Starts the HTTP server of this process, then starts or reuses the bridge and publishes the URL of the server to it.
     *
     * @throws FrontendUnavailableException
     */
    private function joinBridge(Bridge $bridge): void
    {
        $http = ServerManager::instance()->http();

        $http instanceof LaravelHttpServer ? $http->serve() : $http->start();

        $backendUrl = $http instanceof LaravelHttpServer
            ? sprintf('http://%s:%d', $http->host, $http->port)
            : $http->rewrite('/');

        if (! str_starts_with($backendUrl, 'http')) {
            $bridge->ensureRunning();

            return;
        }

        $bridge->join(self::lane(), $backendUrl);

        $this->registeredLane = self::lane();
    }
}
