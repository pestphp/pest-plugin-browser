<?php

declare(strict_types=1);

it('can get element attributes', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    expect($element->getAttribute('type'))->toBe('text');
    expect($element->getAttribute('name'))->toBe('username');
    expect($element->getAttribute('value'))->toBe('johndoe');
});

it('returns null for non-existent attributes', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    expect($element->getAttribute('nonexistent'))->toBeNull();
});
