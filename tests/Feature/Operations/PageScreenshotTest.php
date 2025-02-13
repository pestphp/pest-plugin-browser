<?php declare(strict_types=1);

use function Pest\Browser\visit;

it('takes a full page screenshot of laravel.com', function () {

    $filePath = __DIR__ . '/laravel-full-page.png';

    visit('https://laravel.com')
        ->pageScreenshot(
            path: $filePath,
            fullPage: true
        );

    expect(file_exists($filePath))
        ->toBeTrue();

    unlink($filePath);

});

it('takes a screenshot of laravel.com', function () {

    $filePath = __DIR__.'/laravel.png';

    visit('https://laravel.com')
        ->pageScreenshot(
            path: $filePath
        );

    expect(file_exists($filePath))
        ->toBeTrue();

    unlink($filePath);

});
