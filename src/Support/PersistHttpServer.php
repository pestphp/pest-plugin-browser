<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use JsonException;
use Pest\Plugins\Parallel;

final class PersistHttpServer
{
    public const string DEFAULT_HOST = '127.0.0.1';

    public static ?string $host = null;

    public static ?string $bindAddress = null;

    public static function cleanup(): void
    {
        @unlink(self::path());
    }

    public static function host(): string
    {
        return self::$host ?? self::DEFAULT_HOST;
    }

    public static function bindAddress(): ?string
    {
        return self::$bindAddress;
    }

    public static function port(): int
    {
        return 0;
    }

    public static function persistIfNeeded(): void
    {
        self::persist(self::$host, self::$bindAddress);
    }

    public static function persist(?string $host, ?string $bindAddress): void
    {
        $data = array_filter([
            'host' => $host,
            'bindAddress' => $bindAddress,
        ]);
        if ($data === []) {
            return;
        }

        $path = self::path();
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * @return array{ 'host'?: ?string, 'bindAddress'?: ?string }
     *
     * @throws JsonException
     */
    public static function persisted(): array
    {
        $path = self::path();
        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function wantsPersisted(): bool
    {
        return Parallel::isWorker();
    }

    public static function reset(): void
    {
        self::$host = null;
        self::$bindAddress = null;
    }

    private static function path(): string
    {
        return TempDirectory::path('http-server.json');
    }
}
