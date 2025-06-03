<?php

declare(strict_types=1);

it('can type into input fields', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Password');
    $element = $locator->elementHandle();

    $element->type('password123');
    expect($element->inputValue())->toBe('password123');
});
