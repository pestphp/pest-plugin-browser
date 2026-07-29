<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\TestSuite;

/**
 * @internal
 */
final class Source
{
    /**
     * Return the path to the sources' directory.
     */
    public static function dir(): string
    {
        return TestSuite::getInstance()->rootPath
            .'/tests/Browser/Source';
    }

    /**
     * Return the full path for a source file.
     */
    public static function path(string $filename): string
    {
        $filename = self::dir().'/'.mb_ltrim($filename, '/');

        // check if there is extension, if not, add .html
        if (pathinfo($filename, PATHINFO_EXTENSION) === '') {
            $filename .= '.html';
        }

        return $filename;
    }

    /**
     * Save the page source to the filesystem.
     */
    public static function save(string $content, ?string $filename = null): string
    {
        if ($filename === null) {
            // @phpstan-ignore-next-line
            $filename = str_replace('__pest_evaluable_', '', test()->name());
        }

        if (is_dir(self::dir()) === false) {
            @mkdir(self::dir(), 0755, true);
        }

        file_put_contents(self::path($filename), $content);

        return $filename;
    }

    /**
     * Clean up the sources directory.
     *
     * @codeCoverageIgnore
     */
    public static function cleanup(): void
    {
        if (is_dir(self::dir()) === false) {
            return;
        }

        $files = glob(self::dir().'/*');

        if (is_array($files)) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }

        @rmdir(self::dir());
    }
}
