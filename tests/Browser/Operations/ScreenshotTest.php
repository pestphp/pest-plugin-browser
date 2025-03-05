<?php

declare(strict_types=1);

describe('screenshot', function () {
    it('takes a screenshot', function ($filename, $expectedFilename) {
        $basePath = mb_rtrim((string) $_ENV['PEST_BROWSER_PLUGIN_SCREENSHOT_DIR'], '/');

        $this->visit(playgroundUrl())
            ->screenshot($filename);

        expect(file_exists("{$basePath}/{$expectedFilename}"))->toBeTrue();
    })->with(function () {
        $uniqueFileName = uniqid('screenshot_');

        return [
            [$uniqueFileName, "{$uniqueFileName}.png"],
            [null, 'screenshot__→_it_takes_a_screenshot.png'],
        ];
    });
});
