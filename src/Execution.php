<?php

declare(strict_types=1);

namespace Pest\Browser;

use Amp\ByteStream\ReadableResourceStream;
use Pest\Browser\Playwright\Playwright;
use Pest\Support\Container;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestStatus\TestStatus;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;

use function Amp\async;
use function Amp\delay;

/**
 * @internal
 */
final class Execution
{
    /**
     * Either the current context
     */
    private bool $waiting = false;

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
     * Debugs the test execution by waiting for a key press.
     */
    public function debug(TestStatus $status): void
    {
        $this->waiting = true;

        // @phpstan-ignore-next-line
        $testName = str_replace('__pest_evaluable_', '', test()->name());
        $message = $status->message();

        // @phpstan-ignore-next-line
        Container::getInstance()->get(OutputInterface::class)->writeln(
            "\n  <info>Test [{$testName}] failed with the message:</info> {$message}\n"
        );

        $this->waitForKey();
    }

    /**
     * Pauses the execution.
     */
    public function wait(int|float $seconds = 1): void
    {
        async(function () use ($seconds): void {
            $this->waiting = true;

            delay($seconds);

            $this->waiting = false;
        })->await();
    }

    /**
     * Checks if the execution is paused.
     */
    public function isWaiting(): bool
    {
        return $this->waiting;
    }

    /**
     * Ticks the execution.
     */
    public function tick(): void
    {
        delay(0);
    }

    /**
     * Waits for a key press.
     */
    public function waitForKey(): void
    {
        $this->waiting = true;

        // @phpstan-ignore-next-line
        Container::getInstance()->get(OutputInterface::class)->writeln(
            '  <info>Press any key to continue...</info>'
        );

        // The stream is not closed, as doing so would close the process' STDIN for the rest of the test suite...
        $stdin = new ReadableResourceStream(STDIN);

        async(function () use ($stdin): void {
            $stdin->read();

            delay(0.1);
        })->await();
    }

    /**
     * Awaits for a condition to be met, retrying until the timeout is reached.
     */
    public function waitForExpectation(callable $callback): mixed
    {
        $timeout = Playwright::timeout();

        $originalCount = Assert::getCount();

        $start = microtime(true);
        $end = $start + ($timeout / 1_000);

        while (microtime(true) < $end) {
            // Each attempt gets what is left of the configured timeout, not a fixed
            // second. An operation that fits the timeout then finishes on its first
            // attempt instead of being cut off and run again, and the loop still ends
            // when the timeout does.
            $remaining = (int) max(1, ($end - microtime(true)) * 1_000);

            try {
                return Playwright::usingTimeout(min($timeout, $remaining), $callback);
            } catch (ExpectationFailedException) {
                //
            }

            $this->resetAssertions($originalCount);

            self::instance()->tick();
        }

        return $callback();
    }

    /**
     * Resets the assertion count to the original value.
     */
    private function resetAssertions(int $originalCount): void
    {
        if (Assert::getCount() === $originalCount) {
            return;
        }

        $reflector = new ReflectionClass(Assert::class);
        $property = $reflector->getProperty('count');

        // @phpstan-ignore-next-line
        $property->setValue(Assert::class, $originalCount);
    }
}
