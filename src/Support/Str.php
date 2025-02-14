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
        if (mb_strlen($target) < 2) {
            return false;
        }

        // If the first and last characters are not the same, it's not a regex
        if (($delimiter = mb_substr($target, 0, 1)) !== mb_substr($target, -1, 1)) {
            return false;
        }

        // If the delimiter is alphanumeric, it's not a regex
        if (! preg_match('/[^a-zA-Z0-9]/', $delimiter)) {
            return false;
        }

        return true;
    }
}
