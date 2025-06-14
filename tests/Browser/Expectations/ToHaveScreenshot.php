<?php

declare(strict_types=1);

use Pest\Browser\Support\Screenshot;
use PHPUnit\Framework\ExpectationFailedException;

it('passes when page screenshot is taken and file exists', function (): void {
    $page = page('/test/frame-tests');
    $screenshotName = 'screenshot.png';
    expect($page)->toHaveScreenshot($screenshotName);
    expect(file_exists(
        Screenshot::path($screenshotName)
    ))->toBeTrue();
});

it('fails when screenshot path is invalid', function (): void {
    $page = page('/test/frame-tests');
    // Use an invalid path (e.g., directory does not exist or not writable)
    $invalidPath = 'not-the-screenshot.png';
    expect($page)->toHaveScreenshot($invalidPath)->toBeFalse();
})->throws(ExpectationFailedException::class);
