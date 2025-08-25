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
final readonly class AlreadyStartedPlaywrightServer implements PlaywrightServer
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
     * Creates a new instance of the Playwright server with the persisted host and port.
     *
     * @throws JsonException
     */
    public static function fromPersisted(): self
    {
        ['host' => $host, 'port' => $port] = PersistPlaywrightServer::persisted();
        /** @phpstan-ignore-next-line */
        assert(is_string($host) && is_numeric($port), 'Invalid Playwright server data persisted.');

        return new self($host, (int) $port);
    }

    /**
     * Persists the Playwright server instance with the given host and port
     * as already started; this is useful for scenarios where the server is
     * already running and you want to connect to it as if it were started.
     *
     * @throws JsonException
     */
    public static function persist(string $host, int $port): void
    {
        PersistPlaywrightServer::persist($host, $port);
    }

    /**
     * Marks the Playwright server a stopped by removing the persisted state file.
     */
    public static function markAsStopped(): void
    {
        PersistPlaywrightServer::cleanup();
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
        //
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
