<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find elements by test ID within container', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('user-profile');
    $container = $containerLocator->elementHandle();

    $element = $container->getByTestId('user-email');

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->textContent())->toBe('jane.doe@example.com');
});

it('returns null for non-existent test ID', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('profile-section');
    $container = $containerLocator->elementHandle();

    $element = $container->getByTestId('nonexistent');

    expect($element)->toBeNull();
});
