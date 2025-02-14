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

        if (($delimiter = mb_substr($target, 0, 1)) !== mb_substr($target, -1, 1)) {
            return false;
        }

        return preg_match('/[^a-zA-Z0-9]/', $delimiter) !== false;
    }
}
