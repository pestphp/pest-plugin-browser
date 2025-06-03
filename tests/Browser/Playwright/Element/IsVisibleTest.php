<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('returns true for visible elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');
    $element = $locator->elementHandle();

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->isVisible())->toBeTrue();
});

it('returns false for hidden elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('hidden-element');
    $element = $locator->elementHandle();

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->isVisible())->toBeFalse();
});
