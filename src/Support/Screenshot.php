<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use SplFileObject;

/**
 * @internal
 */
final class Screenshot
{
    private const string DEFAULT_DIR = '/tmp/pest-browser-screenshots';

    /**
     * Return the path to the screenshots directory.
     */
    public static function dir(): string
    {
        // @phpstan-ignore-next-line
        return mb_rtrim((string) $_ENV['PEST_BROWSER_SCREENSHOT_DIR'] ?? self::DEFAULT_DIR, '/');
    }

    /**
     * Return the full path for a screenshot file.
     */
    public static function path(string $filename): string
    {
        return self::dir().'/'.mb_ltrim($filename, '/').'.png';
    }

    /**
     * Save a screenshot to the filesystem.
     */
    public static function save(string $binary, ?string $filename = null): void
    {
        $decodedBinary = (string) base64_decode($binary, true);

        if ($filename === null) {
            // @phpstan-ignore-next-line
            $filename = mb_str_replace('__pest_evaluable__', '', test()->name());
        }

        if (is_dir(self::dir()) === false) {
            mkdir(self::dir(), 0775, true);
        }

        $file = new SplFileObject(self::path($filename), 'wb');
        $file->fwrite($decodedBinary);
    }

    /**
     * Clean up the screenshots directory.
     */
    public static function cleanup(): void
    {
        if (is_dir(self::dir()) === false) {
            return;
        }

        $files = glob(self::dir().'/*.png');

        if (is_array($files)) {
            foreach ($files as $file) {
                unlink($file);
            }
        }

        rmdir(self::dir());
    }
}
