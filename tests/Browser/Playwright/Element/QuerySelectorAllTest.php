<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find multiple child elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('user-profile');
    $container = $containerLocator->elementHandle();

    $elements = $container->querySelectorAll('p');

    expect($elements)->toBeArray();
    expect(count($elements))->toBeGreaterThan(0);

    foreach ($elements as $element) {
        expect($element)->toBeInstanceOf(Element::class);
    }
});

it('returns empty array when no elements match', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('profile-section');
    $container = $containerLocator->elementHandle();

    $elements = $container->querySelectorAll('.nonexistent');

    expect($elements)->toBeArray();
    expect($elements)->toBeEmpty();
});
