<?php

declare(strict_types=1);

namespace Pest\Browser\Recorder;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class TestWriter
{
    /**
     * @return string[]
     */
    public function findExistingTestFiles(string $testsDirectory): array
    {
        if (! is_dir($testsDirectory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($testsDirectory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            assert($file instanceof SplFileInfo);

            if ($file->isFile() && str_ends_with($file->getFilename(), 'Test.php')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    public function write(string $path, string $testCode): void
    {
        if (! file_exists($path)) {
            $dir = dirname($path);

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($path, "<?php\n\ndeclare(strict_types=1);\n\n{$testCode}\n");

            return;
        }

        $existing = file_get_contents($path);
        assert(is_string($existing));

        file_put_contents($path, rtrim($existing) . "\n\n{$testCode}\n");
    }
}
