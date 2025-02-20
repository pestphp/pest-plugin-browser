<?php

declare(strict_types=1);

use Pest\TestSuite;

it('takes a screenshot', function (): void {
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    $this->visit('/')
        ->screenshot('index.png');

    expect(file_exists($basePath.'/index.png'))->toBeTrue();
});

it('takes a screenshot and generates a path', function (): void {
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    $this->visit('/')
        ->screenshot();

    $files = glob($basePath.DIRECTORY_SEPARATOR.'*'.test()->getDescription().'*');

    expect(count($files))->toBe(1);
});
