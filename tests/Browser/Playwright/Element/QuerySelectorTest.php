<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find child elements with CSS selectors', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('profile-section');
    $container = $containerLocator->elementHandle();

    $button = $container->querySelector('button');

    expect($button)->toBeInstanceOf(Element::class);
    expect($button->isVisible())->toBeTrue();
});

it('returns null when no element matches', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('profile-section');
    $container = $containerLocator->elementHandle();

    $element = $container->querySelector('.nonexistent');

    expect($element)->toBeNull();
});
