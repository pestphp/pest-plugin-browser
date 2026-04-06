<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\TestSuite;

/**
 * @internal
 */
final class Video
{
    /**
     * Return the path to the videos directory.
     */
    public static function dir(): string
    {
        return TestSuite::getInstance()->rootPath
            .'/tests/Browser/Videos';
    }

    /**
     * Handles a recorded video after a test completes.
     *
     * If the test failed, moves the video to the videos directory with the test name.
     * If the test passed, deletes the temporary video file to save disk space.
     */
    public static function handleRecording(string $tempDir, string $destName, bool $testFailed): void
    {
        $result = glob($tempDir.DIRECTORY_SEPARATOR.'*.webm');
        /** @var array<int, string> $videos */
        $videos = $result !== false ? $result : [];

        if (! $testFailed || $videos === []) {
            foreach ($videos as $video) {
                @unlink($video);
            }
            @rmdir($tempDir);

            return;
        }

        if (is_dir(self::dir()) === false) {
            mkdir(self::dir(), 0755, true);
        }

        $destFile = self::dir().DIRECTORY_SEPARATOR.$destName.'.webm';

        // Remove any existing video for this test so re-runs stay clean
        if (file_exists($destFile)) {
            unlink($destFile);
        }

        rename($videos[0], $destFile);

        // Clean up extra videos if multiple pages were recorded in the same context
        foreach (array_slice($videos, 1) as $extra) {
            @unlink($extra);
        }

        @rmdir($tempDir);
    }
}
