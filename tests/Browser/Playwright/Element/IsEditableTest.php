<?php

declare(strict_types=1);

it('returns true for editable input elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    expect($element->isEditable())->toBeTrue();
});

it('returns false for readonly input elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('readonly-input');
    $element = $locator->elementHandle();

    expect($element->isEditable())->toBeFalse();
});
