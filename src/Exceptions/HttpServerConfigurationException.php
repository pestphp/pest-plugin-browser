<?php

declare(strict_types=1);

namespace Pest\Browser\Exceptions;

use RuntimeException;

final class HttpServerConfigurationException extends RuntimeException
{
    public static function portIsNotValid(string $port): self
    {
        return new self("The port '{$port}' is not a valid port number.");
    }
}
