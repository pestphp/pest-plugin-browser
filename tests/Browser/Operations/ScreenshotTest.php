<?php

declare(strict_types=1);

use Pest\TestSuite;

use function Pest\Browser\visit;

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

    $testName = mb_ltrim(test()->name(), '__pest_evaluable_'); // @phpstan-ignore-line

    $files = glob($basePath.DIRECTORY_SEPARATOR.'*'.$testName.'*');

    expect(count($files))->toBe(1);
});
