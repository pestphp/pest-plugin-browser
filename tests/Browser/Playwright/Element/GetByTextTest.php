<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find elements by text content within container', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('user-profile');
    $container = $containerLocator->elementHandle();

    $element = $container->getByText('Jane Doe');

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->isVisible())->toBeTrue();
});

it('returns null for non-existent text', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('profile-section');
    $container = $containerLocator->elementHandle();

    $element = $container->getByText('Non-existent text');

    expect($element)->toBeNull();
});
