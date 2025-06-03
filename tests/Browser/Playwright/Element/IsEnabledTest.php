<?php

declare(strict_types=1);

it('returns true for enabled input elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    expect($element->isEnabled())->toBeTrue();
});

it('returns false for disabled input elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('disabled-input');
    $element = $locator->elementHandle();

    expect($element->isEnabled())->toBeFalse();
});
