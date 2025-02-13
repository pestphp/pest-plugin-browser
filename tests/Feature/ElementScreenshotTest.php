<?php declare(strict_types=1);

use function Pest\Browser\visit;

test('it takes a screenshot of an element', function (): void {
    $filePath = __DIR__.'/footer.png';

    visit('https://laravel.com')
        ->elementScreenshot('footer', $filePath);


    expect(file_exists($filePath))
        ->toBeTrue();

    unlink('footer.png');
});
