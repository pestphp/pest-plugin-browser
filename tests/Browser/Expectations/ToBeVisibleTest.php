<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('passes when element is visible', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="visible"]'))->toBeVisible();
});

it('fails when element is visible', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="visible"]'))->not->toBeVisible();
})->throws(ExpectationFailedException::class);

it('passes when element is not visible', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="hidden"]'))->not->toBeVisible();
});

it('fails when element is not visible', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="hidden"]'))->toBeVisible();
})->throws(ExpectationFailedException::class);
