<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright\Servers;

use JsonException;
use Pest\Browser\Contracts\PlaywrightServer;
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
        $path = self::path();

        $path = file_get_contents($path);

        if ($path === false) {
            throw new RuntimeException('Could not read Playwright server data from file.');
        }

        // @phpstan-ignore-next-line
        ['host' => $host, 'port' => $port] = json_decode($path, true, 512, JSON_THROW_ON_ERROR);

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
        $data = [
            'host' => $host,
            'port' => $port,
        ];

        $path = self::path();

        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * Marks the Playwright server a stopped by removing the persisted state file.
     */
    public static function markAsStopped(): void
    {
        $path = self::path();

        @unlink($path);
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

    /**
     * Returns the state file of the Playwright server.
     */
    private static function path(): string
    {
        return dirname(__DIR__, 3).'/.temp/playwright-server.json';
    }
}
