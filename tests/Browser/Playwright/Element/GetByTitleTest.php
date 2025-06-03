<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find elements by title within container', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('user-profile');
    $container = $containerLocator->elementHandle();

    $element = $container->getByTitle('User\'s Name');

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->textContent())->toContain('Jane Doe');
});

it('supports exact matching', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('user-profile');
    $container = $containerLocator->elementHandle();

    $element = $container->getByTitle('User\'s Name', true);

    expect($element)->toBeInstanceOf(Element::class);
});
