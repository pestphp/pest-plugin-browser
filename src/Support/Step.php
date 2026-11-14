<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

/**
 * @internal
 */
final class Step
{
    /**
     * The maximum length of each argument shown in a step's title.
     */
    private const int MAX_ARGUMENT_LENGTH = 60;

    /**
     * Returns a readable title for the given method call, such as "assertSee('Welcome')".
     *
     * @param  array<array-key, mixed>  $arguments
     */
    public static function title(string $method, array $arguments): string
    {
        $arguments = array_map(self::argument(...), $arguments);

        return $method.'('.implode(', ', $arguments).')';
    }

    /**
     * Returns a readable representation of the given argument.
     */
    private static function argument(mixed $argument): string
    {
        $value = match (true) {
            is_string($argument) => "'".$argument."'",
            is_bool($argument) => $argument ? 'true' : 'false',
            $argument === null => 'null',
            is_int($argument), is_float($argument) => (string) $argument,
            is_array($argument) => (string) json_encode($argument),
            is_object($argument) => $argument::class,
            default => get_debug_type($argument),
        };

        return mb_strlen($value) > self::MAX_ARGUMENT_LENGTH
            ? mb_substr($value, 0, self::MAX_ARGUMENT_LENGTH - 1).'…'
            : $value;
    }
}
