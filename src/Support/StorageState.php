<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\TestSuite;

/**
 * @internal
 */
final class StorageState
{
    /**
     * Return the path to the storage state directory.
     */
    public static function dir(): string
    {
        return TestSuite::getInstance()->rootPath
            .'/tests/Browser/StorageState';
    }

    /**
     * Return the full path for a storage state file.
     */
    public static function path(string $name): string
    {
        return self::dir().DIRECTORY_SEPARATOR.mb_ltrim($name, '/').'.json';
    }

    /**
     * Save a storage state JSON string to the filesystem.
     */
    public static function save(string $json, ?string $name = null): string
    {
        if ($name === null) {
            // @phpstan-ignore-next-line
            $name = str_replace('__pest_evaluable_', '', test()->name());
        }

        if (is_dir(self::dir()) === false) {
            mkdir(self::dir(), 0755, true);
        }

        file_put_contents(self::path($name), $json);

        return $name;
    }
}
