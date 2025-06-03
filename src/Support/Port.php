<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use RuntimeException;

/**
 * @internal
 */
final readonly class Port
{
    /**
     * Finds the first available port in the specified range.
     */
    public static function findAvailable(string $host, int $startPort, int $endPort): int
    {
        for ($port = $startPort; $port <= $endPort; $port++) {
            if (self::isPortAvailable($host, $port)) {
                return $port;
            }
        }

        throw new RuntimeException("No available port found between [{$startPort}, {$endPort}]");
    }

    /**
     * Checks if a port is available.
     */
    public static function isPortAvailable(string $host, int $port): bool
    {
        $ch = curl_init("http://{$host}:{$port}");

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        curl_exec($ch);

        $err = curl_errno($ch);

        curl_close($ch);

        return $err !== 0;
    }
}
