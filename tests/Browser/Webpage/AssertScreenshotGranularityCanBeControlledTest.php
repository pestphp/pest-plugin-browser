<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

beforeEach()->skipOnWindows()->skipOnCI();

it('may match a screenshot with zero granularity', function (): void {
    pest()->browser()->screenshotMaxDiffPixelRatio(0)
        ->screenshotMaxDiffPixels(0)
        ->screenshotThreshold(0);
    Route::get('/', fn (): string => '
        <div>
            <h1>1</h1>
        </div>
    ');

    $page = visit('/');

    $page->assertScreenshotMatches();
});

it('may match a screenshot with some tolerance', function (): void {
    pest()->browser()->screenshotMaxDiffPixelRatio(0.1)
        ->screenshotMaxDiffPixels(20)
        ->screenshotThreshold(0.3);
    // The initial snapshot was primed with:
    //    Route::get('/', fn (): string => '
    //        <div>
    //            <h1>1</h1>
    //        </div>
    //    ');
    //
    //    $page = visit('/');
    //    $page->assertScreenshotMatches();

    Route::get('/', fn (): string => '
        <div>
            <h1>1.</h1>
        </div>
    ');

    $page = visit('/');

    $page->assertScreenshotMatches();
});

it('does not match a screenshot when outside the tolerance', function (): void {
    pest()->browser()->screenshotMaxDiffPixelRatio(0.1)
        ->screenshotMaxDiffPixels(null)
        ->screenshotThreshold(0.3);
    // The initial snapshot was primed with:
    //    Route::get('/', fn (): string => '
    //        <div>
    //            <h1>1</h1>
    //        </div>
    //    ');
    //
    //    $page = visit('/');
    //    $page->assertScreenshotMatches();

    // change it to <h1>1.2</h1> to ensure it falls outwith the tolerances
    Route::get('/', fn (): string => '
        <div>
            <h1>1.2</h1>
        </div>
    ');

    $page = visit('/');

    // ideally we'd have an assertScreenshotDoesNotMatch() method
    $this->expectException(ExpectationFailedException::class);
    $this->expectExceptionMessage('Screenshot does not match the last one');
    $page->assertScreenshotMatches();
});
