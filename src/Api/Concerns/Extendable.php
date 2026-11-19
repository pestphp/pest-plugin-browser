<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Closure;
use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;
use Pest\Browser\Api\Webpage;
use Pest\Browser\Exceptions\MethodAlreadyExistsException;
use ReflectionFunction;
use ReflectionType;

/**
 * @internal
 */
trait Extendable
{
    /**
     * The list of extends.
     *
     * @var array<string, Closure>
     */
    private static array $extends = [];

    /**
     * Register a new extend.
     *
     * @param-closure-this self $extend
     *
     * @throws MethodAlreadyExistsException
     */
    public static function extend(string $name, Closure $extend): void
    {
        if (self::hasBuiltInMethod($name)) {
            throw new MethodAlreadyExistsException(
                "Cannot extend the browser with [$name], as a method with that name already exists.",
            );
        }

        self::$extends[$name] = $extend;
    }

    /**
     * Checks if given extend name is registered.
     */
    public static function hasExtend(string $name): bool
    {
        return array_key_exists($name, self::$extends);
    }

    /**
     * Checks if the given extend declares no return type, or `void`, so its `null` keeps the chain going.
     */
    private static function returnsNothing(Closure $extend): bool
    {
        $type = new ReflectionFunction($extend)->getReturnType();

        return ! $type instanceof ReflectionType || (string) $type === 'void';
    }

    /**
     * Checks if a method with the given name exists on any of the objects returned by `visit()`.
     */
    private static function hasBuiltInMethod(string $name): bool
    {
        return method_exists(self::class, $name)
            || method_exists(Webpage::class, $name)
            || method_exists(PendingAwaitablePage::class, $name)
            || method_exists(ArrayablePendingAwaitablePage::class, $name);
    }
}
