<?php

declare(strict_types=1);

use Pest\TestSuite;

it('takes a screenshot', function (): void {
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    $this->visit(htmlfixture('default'))
        ->screenshot('laravel.png');

    expect(file_exists($basePath.'/laravel.png'))->toBeTrue();
});

it('takes a screenshot and generates a path', function (): void {
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    $this->visit(htmlfixture('default'))
        ->screenshot();

    $testName = mb_ltrim(test()->name(), '__pest_evaluable_'); // @phpstan-ignore-line

    $files = glob($basePath.DIRECTORY_SEPARATOR.'*'.$testName.'*');

    expect(count($files))->toBe(1);
});
