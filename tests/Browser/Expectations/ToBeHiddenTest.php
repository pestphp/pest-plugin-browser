<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('passes when element is hidden', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="hidden"]'))->toBeHidden();
});

it('fails when element is hidden', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="hidden"]'))->not->toBeHidden();
})->throws(ExpectationFailedException::class);

it('passes when element is not hidden', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="visible"]'))->not->toBeHidden();
});

it('fails when element is not hidden', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="visible"]'))->toBeHidden();
})->throws(ExpectationFailedException::class);
