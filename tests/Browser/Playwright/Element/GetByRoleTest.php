<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find elements by role within container', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('user-profile');
    $container = $containerLocator->elementHandle();

    $element = $container->getByRole('button', ['name' => 'Edit Profile']);

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->isVisible())->toBeTrue();
});

it('returns null for non-existent role', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('profile-section');
    $container = $containerLocator->elementHandle();

    $element = $container->getByRole('tab', ['name' => 'Non-existent']);

    expect($element)->toBeNull();
});
