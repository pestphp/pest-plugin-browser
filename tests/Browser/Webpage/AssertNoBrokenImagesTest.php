<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('asserts that there are broken images', function (): void {
    Route::get('/', fn (): string => '
        <img src="assets/not-an-image.png" alt="Broken Image">
    ');

    $page = visit('/');

    $page->assertNoBrokenImages();

})->throws(ExpectationFailedException::class, 'assets/not-an-image.png');

it('asserts that there are no broken images', function (): void {

    $image = file_get_contents(__DIR__.'/../../Fixtures/v4.jpg');

    Route::get('/', fn (): string => '
        <div>
            <img src="data:image/jpeg;base64,'.base64_encode($image).'" alt="Valid Image">
        </div>
    ');

    $page = visit('/');

    $page->assertNoBrokenImages();
});

it('asserts that there are is only one missing image', function (): void {

    $image = file_get_contents(__DIR__.'/../../Fixtures/v4.jpg');

    Route::get('/', fn (): string => '
        <div>
            <img src="data:image/jpeg;base64,'.base64_encode($image).'" alt="Valid Image">
            <img src="assets/not-an-image.png" alt="Broken Image">
        </div>
    ');

    $page = visit('/');

    $page->assertNoBrokenImages();
})->throws(ExpectationFailedException::class, 'but found 1:');
