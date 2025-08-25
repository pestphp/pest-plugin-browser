<?php

declare(strict_types=1);

namespace Pest\Browser\Contracts;

use Throwable;

/**
 * @internal
 */
interface HttpServer
{
    /**
     * Creates a new server instance from persisted state.
     */
    public static function fromPersisted(int $port): self;

    /**
     * Starts the server.
     */
    public function start(): void;

    /**
     * Stops the server.
     */
    public function stop(): void;

    /**
     * Rewrites the given URL to the server's URL.
     */
    public function rewrite(string $url): string;

    /**
     * Flushes the server's state.
     */
    public function flush(): void;

    /**
     * Boots the server.
     */
    public function bootstrap(): void;

    /**
     * The last throwable that occurred during the server's execution.
     */
    public function lastThrowable(): ?Throwable;

    /**
     * Throws the last throwable if it should be thrown.
     *
     * @throws Throwable
     */
    public function throwLastThrowableIfNeeded(): void;
}
