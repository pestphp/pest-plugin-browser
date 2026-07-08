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
     * The path to the screenshots' directory.
     */
    private static ?string $dir = null;

    /**
     * Set the path to the screenshots' directory.
     */
    public static function useDirectory(string $dir): void
    {
        self::$dir = $dir;
    }

    /**
     * Return the path to the screenshots' directory.
     */
    public static function dir(): string
    {
        if (self::$dir !== null) {
            return self::$dir;
        }

        return TestSuite::getInstance()->rootPath
            .'/tests/Browser/Screenshots';
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
    public static function save(string $binary, ?string $filename = null): string
    {
        $decodedBinary = (string) base64_decode($binary, true);

        if ($filename === null) {
            // @phpstan-ignore-next-line
            $filename = str_replace('__pest_evaluable_', '', test()->name());
        }

        if (is_dir(self::dir()) === false) {
            @mkdir(self::dir(), 0755, true);
        }

        file_put_contents(self::path($filename), $decodedBinary);

        return $filename;
    }

    /**
     * Clean up the screenshots directory.
     *
     * @codeCoverageIgnore
     */
    public static function cleanup(): void
    {
        if (is_dir(self::dir()) === false) {
            return;
        }

        foreach ([
            self::dir().'/Sliders',
            self::dir().'/ImageDiffView',
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
