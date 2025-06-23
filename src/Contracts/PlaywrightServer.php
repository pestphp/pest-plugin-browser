<?php

declare(strict_types=1);

namespace Pest\Browser\Contracts;

use Pest\Browser\Exceptions\ServerNotFoundException;

/**
 * @internal
 */
interface PlaywrightServer
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
     * Gets the server URL, if running.
     *
     * @throws ServerNotFoundException
     */
    public function url(): string;
}
