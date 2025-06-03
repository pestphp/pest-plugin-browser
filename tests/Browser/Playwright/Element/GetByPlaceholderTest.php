<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find elements by placeholder within container', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->locator('body');
    $container = $containerLocator->elementHandle();

    $element = $container->getByPlaceholder('Search...');

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->getAttribute('type'))->toBe('text');
});

it('supports exact matching', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->locator('body');
    $container = $containerLocator->elementHandle();

    $element = $container->getByPlaceholder('Search...', true);

    expect($element)->toBeInstanceOf(Element::class);
});
