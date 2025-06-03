<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find elements by alt text within container', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('user-profile');
    $container = $containerLocator->elementHandle();

    $element = $container->getByAltText('Profile Picture');

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->isVisible())->toBeTrue();
});

it('supports exact matching', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('user-profile');
    $container = $containerLocator->elementHandle();

    $element = $container->getByAltText('Profile Picture', true);

    expect($element)->toBeInstanceOf(Element::class);
});
