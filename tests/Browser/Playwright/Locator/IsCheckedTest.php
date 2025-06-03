<?php

declare(strict_types=1);

it('returns true for checked checkboxes', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('checked-checkbox');

    expect($locator->isChecked())->toBeTrue();
});

it('returns false for unchecked checkboxes', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('unchecked-checkbox');

    expect($locator->isChecked())->toBeFalse();
});
