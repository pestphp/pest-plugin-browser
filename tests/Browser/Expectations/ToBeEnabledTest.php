<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('passes when input is enabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="enabled-input"]'))->toBeEnabled();
});

it('fails when input is enabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="enabled-input"]'))->not->toBeEnabled();
})->throws(ExpectationFailedException::class);

it('passes when input is not enabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="disabled-input"]'))->not->toBeEnabled();
});

it('fails when input is not enabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="disabled-input"]'))->toBeEnabled();
})->throws(ExpectationFailedException::class);

it('passes when button is enabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('button[name="enabled-button"]'))->toBeEnabled();
});

it('passes when button is not enabled', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('button[name="disabled-button"]'))->not->toBeEnabled();
});
