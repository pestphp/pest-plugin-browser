<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use JsonException;
use Pest\Plugins\Parallel;
use RuntimeException;

final class PersistPlaywrightServer
{
    public const string DEFAULT_HOST = '127.0.0.1';

    public static ?string $host = null;

    public static int $port = 0;

    public static function cleanup(): void
    {
        @unlink(self::path());
    }

    public static function host(): string
    {
        return self::$host ?? self::DEFAULT_HOST;
    }

    public static function port(): int
    {
        return self::$port;
    }

    public static function persist(?string $host, int $port): void
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
     * @return array{ 'host': string, 'port': int}
     *
     * @throws JsonException
     */
    public static function persisted(): array
    {
        $path = self::path();
        $data = file_get_contents($path);
        if ($data === false) {
            throw new RuntimeException('Could not read Playwright server data from file.');
        }

        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    public static function reset(): void
    {
        self::$host = null;
        self::$port = 0;
    }

    /**
     * If we are running in a parallel worker, we want to use the
     * "already started" Playwright server.
     */
    public static function wantsAlreadyStartedPlaywrightServer(): bool
    {
        return Parallel::isWorker();
    }

    /**
     * Check whether the user wants to use an existing Playwright server.
     * It requires both a host and a port to be set.  If only one is set,
     * the value is passed through to the default Playwright server.
     */
    public static function wantsExistingPlaywrightServer(): bool
    {
        return self::$host !== null && self::$port > 0;
    }

    private static function path(): string
    {
        return TempDirectory::path('playwright-server.json');
    }
}
