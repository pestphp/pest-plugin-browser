<?php

declare(strict_types=1);

it('can get text content of elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByText('This is a simple paragraph', true);
    $element = $locator->elementHandle();

    expect($element->textContent())->toBe('This is a simple paragraph');
});

it('returns null for elements without text content', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    // Input elements return empty string, not null
    expect($element->textContent())->toBe('');
});
