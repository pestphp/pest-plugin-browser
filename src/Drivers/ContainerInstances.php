<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Illuminate\Container\Container;
use IteratorAggregate;
use Traversable;

/**
 * @internal
 *
 * @implements ArrayAccess<array-key, mixed>
 * @implements IteratorAggregate<array-key, mixed>
 */
final class ContainerInstances implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The keys whose entry the container stored at the end of a resolution, as opposed to the ones written by `instance()` or any other caller.
     *
     * @var array<array-key, true>
     */
    private array $built = [];

    /**
     * The number of the last write of each key, so that a reader can tell whether a key was written since it looked.
     *
     * @var array<array-key, int>
     */
    private array $versions = [];

    /**
     * The number of the last write of any wrapper, so that a new wrapper never gives a number that an earlier one gave.
     */
    private static int $writes = 0;

    /**
     * Creates the shared instances of a container, all of them as built by the container when told so.
     *
     * @param  array<array-key, mixed>  $entries
     */
    public function __construct(private array $entries, bool $built)
    {
        if ($built) {
            $this->built = array_fill_keys(array_keys($entries), true);
        }

        foreach (array_keys($entries) as $key) {
            $this->versions[$key] = ++self::$writes;
        }
    }

    /**
     * Returns the number of the last write of the given key, or null when the key has no entry.
     */
    public function version(int|string $key): ?int
    {
        return $this->versions[$key] ?? null;
    }

    /**
     * Determines whether the container itself stored the entry of the given key at the end of a resolution.
     */
    public function isBuilt(int|string $key): bool
    {
        return isset($this->built[$key]) && array_key_exists($key, $this->entries);
    }

    /**
     * Determines whether the given key has an entry, null included.
     */
    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->entries);
    }

    /**
     * Puts back an entry the container built, without asking who writes it.
     */
    public function putBuilt(int|string $key, mixed $value): void
    {
        $this->entries[$key] = $value;
        $this->built[$key] = true;
        $this->versions[$key] = ++self::$writes;
    }

    /**
     * Puts back an entry the test registered, without asking who writes it.
     */
    public function putRegistered(int|string $key, mixed $value): void
    {
        $this->entries[$key] = $value;
        unset($this->built[$key]);
        $this->versions[$key] = ++self::$writes;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->entries[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->entries[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (! is_int($offset) && ! is_string($offset)) {
            $this->entries[] = $value;

            return;
        }

        if (is_object($value) && array_key_exists($offset, $this->entries) && $this->entries[$offset] === $value) {
            return;
        }

        $replacesBuilt = isset($this->built[$offset]);

        $this->entries[$offset] = $value;
        $this->versions[$offset] = ++self::$writes;

        if ($this->writtenWhileResolving($offset, $replacesBuilt)) {
            $this->built[$offset] = true;
        } else {
            unset($this->built[$offset]);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->entries[$offset], $this->built[$offset], $this->versions[$offset]);
    }

    public function count(): int
    {
        return count($this->entries);
    }

    /**
     * @return Traversable<array-key, mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->entries);
    }

    /**
     * Determines whether the container built the value it writes under the given key: it stores it at the end of a
     * resolution, or an `extend()` called from a resolving callback of that same key decorates the value the container
     * built, such as a decorator of `afterResolving()`. Any other write, a fake of the test included, is not a build.
     */
    private function writtenWhileResolving(int|string $key, bool $replacesBuilt): bool
    {
        $writer = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2] ?? [];

        if (($writer['class'] ?? null) !== Container::class) {
            return false;
        }

        if ($writer['function'] === 'resolve') {
            return true;
        }

        return $writer['function'] === 'extend' && $replacesBuilt && array_any(
            debug_backtrace(0),
            static fn (array $frame): bool => ($frame['class'] ?? null) === Container::class
                && $frame['function'] === 'fireResolvingCallbacks'
                && is_scalar($frame['args'][0] ?? null)
                && (string) $frame['args'][0] === (string) $key,
        );
    }
}
