<?php

declare(strict_types=1);

namespace Pest\Browser\Contracts;

/**
 * @internal
 */
interface HttpServer
{
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
     * Returns the server's URL.
     */
    public function url(): string;
}
