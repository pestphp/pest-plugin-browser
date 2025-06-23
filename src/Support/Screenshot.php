<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\TestSuite;

/**
 * @internal
 */
final class Screenshot
{
    /**
     * Return the path to the screenshots' directory.
     */
    public static function dir(): string
    {
        return TestSuite::getInstance()->rootPath
            .'/tests/Browser/screenshots';
    }

    /**
     * Return the full path for a screenshot file.
     */
    public static function path(string $filename): string
    {
        $filename = self::dir().'/'.mb_ltrim($filename, '/');

        // check if there is extension, if not, add .png
        if (pathinfo($filename, PATHINFO_EXTENSION) === '') {
            $filename .= '.png';
        }

        return $filename;
    }

    /**
     * Save a screenshot to the filesystem.
     */
    public static function save(string $binary, ?string $filename = null): void
    {
        $decodedBinary = (string) base64_decode($binary, true);

        if ($filename === null) {
            // @phpstan-ignore-next-line
            $filename = str_replace('__pest_evaluable__', '', test()->name());
        }

        if (is_dir(self::dir()) === false) {
            mkdir(self::dir(), 0755, true);
        }

        file_put_contents(self::path($filename), $decodedBinary);
    }

    /**
     * Clean up the screenshots directory.
     */
    public static function cleanup(): void
    {
        if (is_dir(self::dir()) === false) {
            return;
        }

        foreach ([
            self::dir().'/.sliders',
            self::dir(),
        ] as $dir) {
            $files = glob($dir.'/*');

            if (is_array($files)) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }

            @rmdir($dir);
        }
    }
}
