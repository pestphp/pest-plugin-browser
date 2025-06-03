<?php

declare(strict_types=1);

it('can scroll element into view', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('scroll-target');
    $element = $locator->elementHandle();

    $element->scrollIntoViewIfNeeded();
    expect($element->isVisible())->toBeTrue();
});
