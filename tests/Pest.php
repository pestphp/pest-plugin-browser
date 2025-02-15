<?php

declare(strict_types=1);

use Pest\TestSuite;

pest()->beforeAll(function () {
    cleanupScreenshots();
})
    ->afterAll(function () {
        cleanupScreenshots();
    });

function cleanupScreenshots(): void
{
    foreach (glob(TestSuite::getInstance()->testPath.'/Browser/screenshots/*') as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }

    rmdir(TestSuite::getInstance()->testPath.'/Browser/screenshots');
}
