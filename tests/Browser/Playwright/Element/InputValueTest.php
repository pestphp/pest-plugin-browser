<?php

declare(strict_types=1);

it('can get input values', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    expect($element->inputValue())->toBe('johndoe');
});
