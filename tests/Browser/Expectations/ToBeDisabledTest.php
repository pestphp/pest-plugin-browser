<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('passes when input is disabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="disabled-input"]'))->toBeDisabled();
});

it('fails when input is disabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="disabled-input"]'))->not->toBeDisabled();
})->throws(ExpectationFailedException::class);

it('passes when input is not disabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="enabled-input"]'))->not->toBeDisabled();
});

it('fails when input is not disabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="enabled-input"]'))->toBeDisabled();
})->throws(ExpectationFailedException::class);

it('passes when button is disabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('button[name="disabled-button"]'))->toBeDisabled();
});

it('passes when checkbox is disabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="disabled-checkbox"]'))->toBeDisabled();
});
