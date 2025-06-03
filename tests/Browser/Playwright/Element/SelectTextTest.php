<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can select text in input fields', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    $element->selectText();
    expect($element)->toBeInstanceOf(Element::class);
});
