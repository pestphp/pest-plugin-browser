<?php

declare(strict_types=1);

use Pest\TestSuite;

use function Pest\Browser\visit;

afterEach(function () {
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    foreach (glob($basePath.'/*') as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }

    rmdir($basePath);
});

it('takes a screenshot', function (): void {
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    visit('https://laravel.com')
        ->screenshot('laravel.png');

    expect(file_exists($basePath.'/laravel.png'))->toBeTrue();
});

it('takes a screenshot and generates a path', function (): void {
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    visit('https://laravel.com')
        ->screenshot();

    $files = glob($basePath.'/*');

    expect(count($files))->toBe(1);
});
