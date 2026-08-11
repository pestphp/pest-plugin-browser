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
     * The environment variable carrying the identifier of the current test run.
     *
     * The main process mints it and exports it before spawning any worker, so
     * every worker resolves the same state file as the run that spawned it,
     * while a second, unrelated test run in the same project resolves its own.
     */
    private const string RUN_ID_ENV = 'PEST_BROWSER_RUN_ID';

    /**
     * How long a state file may go untouched before it is considered abandoned.
     *
     * A run killed by a stall watchdog or a CI timeout never reaches its
     * teardown, so its file would otherwise stay behind forever.
     */
    private const int STALE_AFTER_SECONDS = 21600;

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

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Could not read Playwright server data from file.');
        }

        // @phpstan-ignore-next-line
        ['host' => $host, 'port' => $port] = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

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
        $directory = dirname($path);

        // Two runs may reach this at the same moment, so the result is checked
        // rather than the precondition -- "is_dir" before "mkdir" is a race.
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException(sprintf('Could not create the directory [%s].', $directory));
        }

        self::removeAbandonedStateFiles($path);

        // Written through a temporary file and moved into place, so a worker
        // can never observe a half-written description.
        $temporary = $path.'.'.getmypid().'.tmp';

        if (file_put_contents($temporary, json_encode($data, JSON_THROW_ON_ERROR)) === false) {
            throw new RuntimeException(sprintf('Could not write Playwright server data to [%s].', $temporary));
        }

        if (! rename($temporary, $path)) {
            @unlink($temporary);

            throw new RuntimeException(sprintf('Could not move Playwright server data into [%s].', $path));
        }
    }

    /**
     * Marks the Playwright server a stopped by removing the persisted state file.
     *
     * The file is scoped to the current run, so a test run that finishes while
     * another one is still going can only ever remove its own description.
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
     * Returns the state file of the Playwright server for the current run.
     */
    private static function path(): string
    {
        return dirname(__DIR__, 3).'/.temp/playwright-server-'.self::runId().'.json';
    }

    /**
     * Returns the identifier of the current test run, minting one if this is
     * the process that starts the server.
     */
    private static function runId(): string
    {
        foreach ([$_SERVER, $_ENV] as $source) {
            if (isset($source[self::RUN_ID_ENV]) && is_string($source[self::RUN_ID_ENV]) && $source[self::RUN_ID_ENV] !== '') {
                return self::sanitize($source[self::RUN_ID_ENV]);
            }
        }

        $inherited = getenv(self::RUN_ID_ENV);

        if (is_string($inherited) && $inherited !== '') {
            return self::sanitize($inherited);
        }

        // The random suffix matters as much as the process id: a killed run can
        // leave a state file behind, and process ids are reused.
        $runId = getmypid().'-'.bin2hex(random_bytes(4));

        putenv(self::RUN_ID_ENV.'='.$runId);
        $_SERVER[self::RUN_ID_ENV] = $runId;
        $_ENV[self::RUN_ID_ENV] = $runId;

        return $runId;
    }

    /**
     * Reduces the run identifier to characters that are safe in a file name.
     *
     * It arrives from the environment, so it is not assumed to be well-formed.
     */
    private static function sanitize(string $runId): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $runId);

        return match (true) {
            $sanitized === null, $sanitized === '' => 'default',
            default => mb_substr($sanitized, 0, 64),
        };
    }

    /**
     * Removes state files left behind by runs that never reached their teardown.
     */
    private static function removeAbandonedStateFiles(string $currentPath): void
    {
        // A missing directory answers with an empty array rather than false.
        $files = glob(dirname($currentPath).'/playwright-server-*.json');

        foreach ($files === false ? [] : $files as $file) {
            if ($file === $currentPath) {
                continue;
            }

            $modifiedAt = @filemtime($file);

            if ($modifiedAt !== false && (time() - $modifiedAt) > self::STALE_AFTER_SECONDS) {
                @unlink($file);
            }
        }
    }
}
