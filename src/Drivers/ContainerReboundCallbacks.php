<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers;

use ArrayAccess;
use Closure;
use Illuminate\Container\Container;
use ReflectionFunction;

/**
 * @internal
 *
 * @implements ArrayAccess<array-key, mixed>
 */
final class ContainerReboundCallbacks implements ArrayAccess
{
    /**
     * The callbacks the container called for each watched abstract, keyed by their position among the callbacks of
     * the abstract, in the order of their first call.
     *
     * @var array<array-key, array<int, Closure>>
     */
    private array $notified = [];

    /**
     * Creates the rebound callbacks of a container that watches the given abstracts.
     *
     * @param  array<array-key, mixed>  $callbacks
     * @param  array<array-key, true>  $watched
     */
    public function __construct(private array $callbacks, private readonly array $watched)
    {
        //
    }

    /**
     * Calls the callback the way the container does: arguments that are references reach parameters declared by
     * reference, and scalar arguments are coerced to the types the parameters declare.
     *
     * @param  array<array-key, mixed>  $arguments
     */
    public static function call(Closure $callback, array $arguments): mixed
    {
        return new ReflectionFunction($callback)->invokeArgs($arguments);
    }

    /**
     * Returns the rebound callbacks, keyed by abstract.
     *
     * @return array<array-key, mixed>
     */
    public function callbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * Returns the callbacks the container called for each watched abstract.
     *
     * @return array<array-key, list<Closure>>
     */
    public function notified(): array
    {
        return array_map(array_values(...), $this->notified);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->callbacks[$offset]);
    }

    public function &offsetGet(mixed $offset): mixed
    {
        $reader = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];

        if (($reader['class'] ?? null) === Container::class
            && $reader['function'] === 'getReboundCallbacks'
            && isset($this->watched[$offset])
            && is_array($this->callbacks[$offset] ?? null)) {
            $recorded = $this->recording($offset, $this->callbacks[$offset]);

            return $recorded;
        }

        return $this->callbacks[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->callbacks[] = $value;

            return;
        }

        $this->callbacks[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->callbacks[$offset]);
    }

    /**
     * Wraps the callbacks of the given abstract, so that each one records that the container called it.
     *
     * @param  array<array-key, mixed>  $callbacks
     * @return list<mixed>
     */
    private function recording(int|string $abstract, array $callbacks): array
    {
        $wrapped = [];

        foreach (array_values($callbacks) as $position => $callback) {
            $wrapped[] = $callback instanceof Closure
                ? function (mixed &...$arguments) use ($abstract, $position, $callback): mixed {
                    $this->notified[$abstract][$position] ??= $callback;

                    $references = [];

                    foreach ($arguments as $index => &$argument) {
                        $references[$index] = &$argument;
                    }

                    return self::call($callback, $references);
                }
            : $callback;
        }

        return $wrapped;
    }
}
