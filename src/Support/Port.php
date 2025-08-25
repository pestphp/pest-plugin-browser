<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\Browser\Exceptions\PortNotFoundException;

/**
 * @internal
 */
final class Port
{
    /**
     * Checks if a port is available.
     */
    public static function find(int $port = 0): int
    {
        return self::findPort($port);
    }

    /**
     * Checks if a port is valid (an integer between 1 and 65535).
     */
    public static function isValid(string|int|null $port): bool
    {
        if ($port === null) {

            return false;
        }

        if (is_string($port)) {
            $port = (int) $port;
        }

        return $port > 0 && $port < 65536;
    }

    private static function findPort(int $portToTry): int
    {
        $port = false;

        $sock = socket_create_listen($portToTry);

        if ($sock !== false) {
            socket_getsockname($sock, $addr, $port);

            socket_close($sock);
        }

        if ($port === false) {
            // @codeCoverageIgnoreStart
            throw new PortNotFoundException('Unable to find an available port.');
            // @codeCoverageIgnoreEnd
        }

        assert(is_int($port));

        return $port;
    }
}
