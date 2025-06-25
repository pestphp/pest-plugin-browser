<?php

declare(strict_types=1);

namespace Pest\Browser;

use React\EventLoop\Loop;

use function React\Async\async;
use function React\Async\await;
use function React\Promise\Timer\sleep;

/**
 * @internal
 */
final class Execution
{
    /**
     * Either the current context
     */
    private bool $paused = false;

    /**
     * The current context instance, or null if not set.
     */
    private static ?Execution $instance = null;

    /**
     * Creates a new context instance.
     */
    public static function instance(): self
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Pauses the execution.
     */
    public function pause(int|float $seconds = 1): void
    {
        $this->paused = true;

        try {
            Loop::get()->futureTick(async(function () use ($seconds): void {
                await(sleep($seconds));

                Loop::stop();
            }));

            Loop::run();
        } finally {
            $this->paused = false;
        }
    }

    /**
     * Checks if the execution is paused.
     */
    public function isPaused(): bool
    {
        return $this->paused;
    }

    /**
     * Ticks the execution.
     */
    public function tick(): void
    {
        Loop::get()->futureTick(async(function (): void {
            if ($this->paused) {
                return;
            }

            Loop::stop();
        }));

        Loop::run();
    }
}
