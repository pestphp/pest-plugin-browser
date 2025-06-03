<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can focus elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    $element->focus();
    expect($element)->toBeInstanceOf(Element::class);
});
