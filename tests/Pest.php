<?php

declare(strict_types=1);

use Pest\TestSuite;

pest()
    ->beforeEach(function () {
        cleanupScreenshots();
        // cleanupArtifacts();
    })
    ->afterEach(function () {
        collectArtifacts();
        cleanupScreenshots();
    });

function playgroundUrl($uri = null): string
{
    return 'http://localhost:9357'.$uri;
}

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

function collectArtifacts(): void
{
    $playwrightPath = TestSuite::getInstance()->testPath.'/../.temp/test-results';
    $pestPath = TestSuite::getInstance()->testPath.'/Browser/artifacts';

    if (! file_exists($pestPath)) {
        mkdir($pestPath, 0755, true);
    }

    if ($dir = glob("{$playwrightPath}/*retry1")[0] ?? false) {
        $test = TestSuite::getInstance()->getDescription();
        if (! file_exists("{$pestPath}/{$test}")) {
            mkdir("{$pestPath}/{$test}", 0755, true);
        }

        foreach (glob("{$dir}/*") as $file) {
            if (is_file($file)) {
                copy($file, "{$pestPath}/{$test}/".basename($file));
            }
        }
    }
}

function cleanupArtifacts(): void
{
    $basePath = TestSuite::getInstance()->testPath.'/Browser/artifacts';

    foreach (glob("$basePath/*") as $file) {
        if (is_file($file)) {
            unlink($file);
        }

        if (is_dir($file)) {
            foreach (glob("$file/*") as $subFile) {
                if (is_file($subFile)) {
                    unlink($subFile);
                }
            }

            file_exists($file) && rmdir($file);
        }
    }

    file_exists($basePath) && rmdir($basePath);
}
