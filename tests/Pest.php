<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;
use Pest\Expectation;
use Pest\TestSuite;

pest()
    ->beforeEach(fn () => cleanupScreenshots())
    ->afterEach(fn () => cleanupScreenshots());

function cleanupScreenshots(): void
{
    $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

    foreach (glob("$basePath/*") as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }

    if (file_exists($basePath)) {
        rmdir($basePath);
    }
}

function playgroundUrl(string $path = '/'): string
{
    return 'http://localhost:9357/'.mb_ltrim($path, '/');
}

// todo: move this to Pest core
expect()->extend('toBeChecked', function (): Expectation {

    expect($this->value)->toBeInstanceOf(Element::class)
        ->and($this->value->isChecked())->toBeTrue();

    return $this;
});

expect()->extend('toBeVisible', function (): Expectation {

    expect($this->value)->toBeInstanceOf(Element::class)
        ->and($this->value->isVisible())->toBeTrue();

    return $this;
});

expect()->extend('toBeEnabled', function (): Expectation {

    expect($this->value)->toBeInstanceOf(Element::class)
        ->and($this->value->isEnabled())->toBeTrue();

    return $this;
});

expect()->extend('toBeDisabled', function (): Expectation {

    expect($this->value)->toBeInstanceOf(Element::class)
        ->and($this->value->isDisabled())->toBeTrue();

    return $this;
});

expect()->extend('toBeEditable', function (): Expectation {

    expect($this->value)->toBeInstanceOf(Element::class)
        ->and($this->value->isEditable())->toBeTrue();

    return $this;
});

expect()->extend('toBeHidden', function (): Expectation {

    expect($this->value)->toBeInstanceOf(Element::class)
        ->and($this->value->isHidden())->toBeTrue();

    return $this;
});
