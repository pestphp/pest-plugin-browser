<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

/**
 * @internal
 */
final class Str
{
    /**
     * Check if the given string is a regex.
     */
    public static function isRegex(string $target): bool
    {
        if (strlen($target) < 2) {
            return false;
        }

        return ($delimiter = substr($target, 0, 1)) === substr($target, -1, 1) && preg_match('/[^a-zA-Z0-9]/', $delimiter);
    }
}
