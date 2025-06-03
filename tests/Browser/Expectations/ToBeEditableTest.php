<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('passes when input is editable', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="enabled-input"]'))->toBeEditable();
});

it('fails when input is editable', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="enabled-input"]'))->not->toBeEditable();
})->throws(ExpectationFailedException::class);

it('passes when input is not editable (readonly)', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="readonly-input"]'))->not->toBeEditable();
});

it('fails when input is not editable (readonly)', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="readonly-input"]'))->toBeEditable();
})->throws(ExpectationFailedException::class);

it('passes when input is not editable (disabled)', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="disabled-input"]'))->not->toBeEditable();
});

it('fails when input is not editable (disabled)', function (): void {
    $page = $this->page()->goto('/test/form-inputs');

    expect($page->locator('input[name="disabled-input"]'))->toBeEditable();
})->throws(ExpectationFailedException::class);
