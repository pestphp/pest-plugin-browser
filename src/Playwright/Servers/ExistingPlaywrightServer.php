<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright\Servers;

use JsonException;
use Pest\Browser\Contracts\PlaywrightServer;
use Pest\Browser\Support\PersistPlaywrightServer;
use RuntimeException;

/**
 * @internal
 */
final class ExistingPlaywrightServer implements PlaywrightServer
{
    /**
     * Creates a new already started playwright server instance.
     */
    public function __construct(
        public string $host,
        public int $port,
    ) {
        //
    }

    /**
     * Creates a new instance of the Playwright server with the user-supplied host and port.
     */
    public static function fromExisting(): self
    {

        if (! self::isValid()) {
            throw new RuntimeException('Playwright server host and port must be set to use an existing server.');
        }

        return new self(PersistPlaywrightServer::host(), PersistPlaywrightServer::port());
    }

    /**
     * Checks if the existing Playwright server host and port are valid.
     */
    public static function isValid(): bool
    {
        return PersistPlaywrightServer::wantsExistingPlaywrightServer();
    }

    /**
     * Persists the Playwright server instance with the given host and port
     * as already started; this is useful for scenarios where the server is
     * already running and you want to connect to it as if it were started.
     *
     * @throws JsonException
     */
    public function persistSelf(): self
    {
        PersistPlaywrightServer::persist($this->host, $this->port);

        return $this;
    }

    /**
     * Starts the process until the given "output" condition is met.
     */
    public function start(): void
    {
        //
    }

    /**
     * Stops the process if it is running.
     */
    public function stop(): void
    {
        PersistPlaywrightServer::cleanup();
    }

    /**
     * Flushes the process.
     */
    public function flush(): void
    {
        //
    }

    /**
     * Returns the URL of the process.
     *
     * @throws RuntimeException If the process has not been started yet or has stopped unexpectedly.
     */
    public function url(): string
    {
        return sprintf('%s:%d', $this->host, $this->port);
    }
}
