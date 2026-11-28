<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use FilesystemIterator;
use Pest\TestSuite;
use ZipArchive;

/**
 * @internal
 */
final class Trace
{
    /**
     * The ZIP archive comment marking a trace that belongs to a failed test.
     */
    private const string FAILED_ARCHIVE_COMMENT = 'pest-browser:failed';

    /**
     * Return the path to the traces' directory.
     */
    public static function dir(): string
    {
        return TestSuite::getInstance()->rootPath
            .'/tests/Browser/Traces';
    }

    /**
     * Return the full path for the trace of the given test.
     */
    public static function path(string $title, int $index = 0): string
    {
        $filename = (string) preg_replace('/[^A-Za-z0-9_\-.]+/', '_', $title);

        if ($index > 0) {
            $filename .= '-'.($index + 1);
        }

        return self::dir().'/'.$filename.'.zip';
    }

    /**
     * Return the given trace path relative to the project's root.
     */
    public static function relative(string $path): string
    {
        return mb_ltrim(str_replace(TestSuite::getInstance()->rootPath, '', $path), '/');
    }

    /**
     * Return the paths of the traces saved during the test run.
     *
     * @return array<int, string>
     */
    public static function saved(): array
    {
        $files = glob(self::dir().'/*.zip');

        if (! is_array($files)) {
            return [];
        }

        // In the order the tests ran, so the first failure is opened first when only failures are kept.
        usort($files, fn (string $a, string $b): int => filemtime($a) <=> filemtime($b));

        return $files;
    }

    /**
     * Return the paths of the traces saved for failed tests during the test run.
     *
     * @return array<int, string>
     */
    public static function failed(): array
    {
        return array_values(array_filter(self::saved(), self::isFailed(...)));
    }

    /**
     * Determine whether the given trace archive belongs to a failed test.
     */
    public static function isFailed(string $path): bool
    {
        if (class_exists(ZipArchive::class) === false) {
            return false;
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return false;
        }

        $comment = $zip->getArchiveComment();

        $zip->close();

        return $comment === self::FAILED_ARCHIVE_COMMENT;
    }

    /**
     * Save a trace archive to the filesystem, along with the PHP call stacks and their sources.
     *
     * @param  array<string, array<int, array{file: string, line: int, function: string}>>  $stacks
     */
    public static function save(string $path, string $archive, array $stacks, bool $failed = false): void
    {
        if (is_dir(self::dir()) === false) {
            @mkdir(self::dir(), 0755, true);
        }

        file_put_contents($path, $archive);

        if (($stacks === [] && ! $failed) || class_exists(ZipArchive::class) === false) {
            return;
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return;
        }

        if ($failed) {
            $zip->setArchiveComment(self::FAILED_ARCHIVE_COMMENT);
        }

        if ($stacks === []) {
            $zip->close();

            return;
        }

        $files = [];
        $serialized = [];

        foreach ($stacks as $id => $frames) {
            $serialized[] = [$id, array_map(function (array $frame) use (&$files): array {
                $files[$frame['file']] ??= count($files);

                return [$files[$frame['file']], $frame['line'], 0, $frame['function']];
            }, $frames)];
        }

        $contents = (string) json_encode(['files' => array_keys($files), 'stacks' => $serialized]);

        // The trace viewer pairs each "<name>.trace" entry with a "<name>.stacks" one, holding the
        // call stacks of the actions, and shows the sources stored as "resources/src@<sha1>.txt".
        $prefixes = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (preg_match('/^(.+)\.trace$/', (string) $zip->getNameIndex($i), $matches) === 1) {
                $prefixes[] = $matches[1];
            }
        }

        foreach ($prefixes as $prefix) {
            $zip->addFromString($prefix.'.stacks', $contents);
        }

        foreach (array_keys($files) as $file) {
            if (is_file($file)) {
                $zip->addFile($file, 'resources/src@'.sha1($file).'.txt');
            }
        }

        $zip->close();
    }

    /**
     * Clean up the traces directory.
     *
     * @codeCoverageIgnore
     */
    public static function cleanup(): void
    {
        $files = glob(self::dir().'/*.zip');

        if (is_array($files)) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }

        $dir = self::dir();

        if (is_dir($dir) && ! new FilesystemIterator($dir)->valid()) {
            rmdir($dir);
        }
    }
}
