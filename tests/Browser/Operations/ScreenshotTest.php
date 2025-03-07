<?php

declare(strict_types=1);

use Pest\Browser\Support\Screenshot;

describe('screenshot', function () {
    it('takes a screenshot', function ($filename, $expectedFilename) {
        $basePath = Screenshot::dir();

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
