<?php

declare(strict_types=1);

it('returns false for unchecked checkbox', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByRole('checkbox', ['name' => 'Remember Me']);
    $element = $locator->elementHandle();

    expect($element->isChecked())->toBeFalse();
});

it('returns true for checked checkbox', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByRole('checkbox', ['name' => 'Remember Me']);
    $element = $locator->elementHandle();

    $element->check();
    expect($element->isChecked())->toBeTrue();
});
