<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers;

use Pest\Browser\Contracts\HttpServer;
use Throwable;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class NullableHttpServer implements HttpServer
{
    public static function fromPersisted(int $port): self
    {
        return new self();
    }

    /**
     * Rewrite the given URL to match the server's host and port.
     */
    public function rewrite(string $url): string
    {
        return $url;
    }

    /**
     * Start the server and listen for incoming connections.
     */
    public function start(): void
    {
        //
    }

    /**
     * Stop the server and close all connections.
     */
    public function stop(): void
    {
        //
    }

    /**
     * Flush pending requests and close all connections.
     */
    public function flush(): void
    {
        //
    }

    /**
     * Bootstrap the server.
     */
    public function bootstrap(): void
    {
        //
    }

    /**
     * Get the last throwable that occurred during the server's execution.
     */
    public function lastThrowable(): ?Throwable
    {
        return null;
    }

    /**
     * Throws the last throwable if it should be thrown.
     */
    public function throwLastThrowableIfNeeded(): void
    {
        //
    }
}
