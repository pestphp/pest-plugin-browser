<?php

declare(strict_types=1);

pest()->afterEach(fn () => cleanupScreenshots());

function cleanupScreenshots(): void
{
    $basePath = mb_rtrim((string) $_ENV['PEST_BROWSER_PLUGIN_SCREENSHOT_DIR'], '/');

    foreach (glob("{$basePath}/*") as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }

    file_exists($basePath) && rmdir($basePath);
}

function playgroundUrl(string $uri = '/'): string
{
    return 'http://localhost:9357/'.ltrim($uri, '/');
}
