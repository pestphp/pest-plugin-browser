<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\Browser\Exceptions\PortNotFoundException;

/**
 * @internal
 */
final readonly class Port
{
    /**
     * Checks if a port is available.
     */
    public static function find(): int
    {
        $port = false;

        $sock = socket_create_listen(0);

        if ($sock !== false) {
            socket_getsockname($sock, $addr, $port);

            socket_close($sock);
        }

        if ($port === false) {
            throw new PortNotFoundException('Unable to find an available port.');
        }

        assert(is_int($port));

        return $port;
    }
}
