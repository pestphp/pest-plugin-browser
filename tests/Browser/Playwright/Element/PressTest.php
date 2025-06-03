<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can press keys', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    $element->focus();

    $element->press('Enter');
    // Key press doesn't change the element state, just verify no error
    expect($element)->toBeInstanceOf(Element::class);
});
