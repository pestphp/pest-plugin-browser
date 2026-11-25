<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers;

use Closure;
use Illuminate\Container\Container;
use ReflectionProperty;
use WeakMap;

/**
 * @internal
 */
final class ContainerScopedInstances
{
    /**
     * The abstracts each closure registered with `scoped()` returns, as the container reads them by reflection.
     *
     * @var WeakMap<Closure, list<string>>|null
     */
    private static ?WeakMap $closureAbstracts = null;

    /**
     * Records from now on who writes each shared instance of the application: the container at the end of a
     * resolution, or the test through `instance()`. The instances the application built while it booted count
     * as built by the container.
     */
    public static function track(): void
    {
        if (! function_exists('app')) {
            return;
        }

        $container = app();

        if (self::readInstances($container) instanceof ContainerInstances) {
            return;
        }

        self::wrap($container, built: true);

        $container->beforeResolving(static function (string $abstract, array $parameters, Container $container): void {
            self::instancesOf($container);
        });
    }

    /**
     * Returns the scoped instances the container holds, keyed by abstract.
     *
     * @return array<array-key, mixed>
     */
    public static function of(Container $container): array
    {
        $instances = self::instancesOf($container);

        $scoped = [];

        foreach (self::scopedAbstracts($container) as $abstract) {
            if ($instances->has($abstract)) {
                $scoped[$abstract] = $instances[$abstract];
            }
        }

        return $scoped;
    }

    /**
     * Returns the number of the last write of each scoped entry the container holds, keyed by abstract.
     *
     * @return array<array-key, int>
     */
    public static function versions(Container $container): array
    {
        $instances = self::instancesOf($container);

        $versions = [];

        foreach (self::scopedAbstracts($container) as $abstract) {
            $version = $instances->version($abstract);

            if ($version !== null) {
                $versions[$abstract] = $version;
            }
        }

        return $versions;
    }

    /**
     * Removes from the container the scoped values it built itself and still holds, and returns them, so that
     * a request of the frontend server builds its own; the ones a test registered stay.
     *
     * @return array<array-key, mixed>
     */
    public static function withdraw(Container $container): array
    {
        $instances = self::instancesOf($container);

        $withdrawn = array_filter(
            self::of($container),
            static fn (mixed $instance, int|string $abstract): bool => $instances->isBuilt($abstract),
            ARRAY_FILTER_USE_BOTH,
        );

        foreach (array_keys($withdrawn) as $abstract) {
            unset($instances[$abstract]);
        }

        return $withdrawn;
    }

    /**
     * Returns the scoped values the test registered itself, keyed by abstract, so that they come out of a request
     * of the frontend server as they went in.
     *
     * @return array<array-key, mixed>
     */
    public static function registered(Container $container): array
    {
        $instances = self::instancesOf($container);

        return array_filter(
            self::of($container),
            static fn (mixed $instance, int|string $abstract): bool => ! $instances->isBuilt($abstract),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * Puts the withdrawn values and the values the test registered back into the container, without the rebound
     * callbacks a new instance fires.
     *
     * @param  array<array-key, mixed>  $withdrawn
     * @param  array<array-key, mixed>  $registered
     */
    public static function restore(Container $container, array $withdrawn, array $registered = []): void
    {
        $instances = self::instancesOf($container);

        foreach ($withdrawn as $abstract => $instance) {
            $instances->putBuilt($abstract, $instance);
        }

        foreach ($registered as $abstract => $instance) {
            $instances->putRegistered($abstract, $instance);
        }
    }

    /**
     * Calls again, with the value the container holds again, each rebound callback the container called during a
     * request of the frontend server, in the same order, skipping a null value the container would build anew.
     *
     * @param  array<array-key, list<Closure>>  $notified
     */
    public static function rebound(Container $container, array $notified): void
    {
        $instances = self::instancesOf($container);

        foreach ($notified as $abstract => $callbacks) {
            $instance = $instances[$abstract];

            if ($instance === null) {
                continue;
            }

            foreach ($callbacks as $callback) {
                $app = $container;

                ContainerReboundCallbacks::call($callback, [&$app, &$instance]);
            }
        }
    }

    /**
     * Starts to record which rebound callbacks of the given abstracts the container calls.
     *
     * @param  list<array-key>  $abstracts
     */
    public static function watchRebound(Container $container, array $abstracts): void
    {
        self::takeRebound($container);

        $property = new ReflectionProperty(Container::class, 'reboundCallbacks');

        $callbacks = $property->getValue($container);

        $property->setValue($container, new ContainerReboundCallbacks(
            is_array($callbacks) ? $callbacks : [],
            array_fill_keys($abstracts, true),
        ));
    }

    /**
     * Returns the rebound callbacks the container called for each watched abstract, and stops recording.
     *
     * @return array<array-key, list<Closure>>
     */
    public static function takeRebound(Container $container): array
    {
        $property = new ReflectionProperty(Container::class, 'reboundCallbacks');

        $callbacks = $property->getValue($container);

        if (! $callbacks instanceof ContainerReboundCallbacks) {
            return [];
        }

        $property->setValue($container, $callbacks->callbacks());

        return $callbacks->notified();
    }

    /**
     * Returns the shared instances of the container, wrapping them again when `forgetInstances()` or `flush()`
     * replaced them with a plain array; the entries of that array were written outside a resolution.
     */
    private static function instancesOf(Container $container): ContainerInstances
    {
        $instances = self::readInstances($container);

        return $instances instanceof ContainerInstances ? $instances : self::wrap($container, built: false);
    }

    /**
     * Replaces the shared instances of the container with a record of who writes them.
     */
    private static function wrap(Container $container, bool $built): ContainerInstances
    {
        $property = new ReflectionProperty(Container::class, 'instances');

        $entries = $property->getValue($container);

        $instances = new ContainerInstances(is_array($entries) ? $entries : [], $built);

        $property->setValue($container, $instances);

        return $instances;
    }

    /**
     * Returns the shared instances of the container as the container holds them.
     */
    private static function readInstances(Container $container): mixed
    {
        return new ReflectionProperty(Container::class, 'instances')->getValue($container);
    }

    /**
     * Returns the abstracts the container registered as scoped.
     *
     * @return list<array-key>
     */
    private static function scopedAbstracts(Container $container): array
    {
        $closureAbstracts = self::$closureAbstracts ??= new WeakMap;

        return (function () use ($closureAbstracts): array {
            $abstracts = [];

            foreach ($this->scopedInstances as $registered) {
                $candidates = $registered instanceof Closure
                    ? $closureAbstracts[$registered] ??= array_values(array_filter($this->closureReturnTypes($registered), is_string(...)))
                    : [$registered];

                foreach ($candidates as $abstract) {
                    if (is_string($abstract) || is_int($abstract)) {
                        $abstracts[] = $abstract;
                    }
                }
            }

            return $abstracts;
        })->call($container);
    }
}
