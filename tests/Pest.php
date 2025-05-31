<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Locator;
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

    if (! $this->value instanceof Element && ! $this->value instanceof Locator) {
        throw new InvalidArgumentException('Expected value to be an Element or Locator instance');
    }

    expect($this->value->isChecked())->toBeTrue();

    return $this;
});

expect()->extend('toBeVisible', function (): Expectation {

    if (! $this->value instanceof Element && ! $this->value instanceof Locator) {
        throw new InvalidArgumentException('Expected value to be an Element or Locator instance');
    }

    expect($this->value->isVisible())->toBeTrue();

    return $this;
});

expect()->extend('toBeEnabled', function (): Expectation {

    if (! $this->value instanceof Element && ! $this->value instanceof Locator) {
        throw new InvalidArgumentException('Expected value to be an Element or Locator instance');
    }

    expect($this->value->isEnabled())->toBeTrue();

    return $this;
});

expect()->extend('toBeDisabled', function (): Expectation {

    if (! $this->value instanceof Element && ! $this->value instanceof Locator) {
        throw new InvalidArgumentException('Expected value to be an Element or Locator instance');
    }

    expect($this->value->isDisabled())->toBeTrue();

    return $this;
});

expect()->extend('toBeEditable', function (): Expectation {

    if (! $this->value instanceof Element && ! $this->value instanceof Locator) {
        throw new InvalidArgumentException('Expected value to be an Element or Locator instance');
    }

    expect($this->value->isEditable())->toBeTrue();

    return $this;
});

expect()->extend('toBeHidden', function (): Expectation {

    if (! $this->value instanceof Element && ! $this->value instanceof Locator) {
        throw new InvalidArgumentException('Expected value to be an Element or Locator instance');
    }

    expect($this->value->isHidden())->toBeTrue();

    return $this;
});
// todo: move this to Pest core end
