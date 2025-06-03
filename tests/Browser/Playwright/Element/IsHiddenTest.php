<?php

declare(strict_types=1);

it('returns false for visible elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');
    $element = $locator->elementHandle();

    expect($element->isHidden())->toBeFalse();
});

it('returns true for hidden elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('hidden-element');
    $element = $locator->elementHandle();

    expect($element->isHidden())->toBeTrue();
});
