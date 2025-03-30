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

function playgroundUrl(string $path = '/'): string
{
    return 'http://localhost:9357/'.mb_ltrim($path, '/');
}
