<?php

declare(strict_types=1);

use Pest\TestSuite;

pest()
    ->beforeEach(fn () => cleanupScreenshots())
    ->afterEach(fn () => cleanupScreenshots());

function cleanupScreenshots(): void
{
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    foreach (glob("$basePath/*") as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }

    file_exists($basePath) && rmdir($basePath);
}

function htmlfixture($filename): string
{
    $file = __DIR__.'/Fixtures/html/'.$filename.'.html';

    // We do so it works with Playwright correctly.
    $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);

    if (! file_exists($file)) {
        throw new RuntimeException("File '{$filename}' not found in fixture path: ({$file}");
    }

    return 'file://'.$file;
}
