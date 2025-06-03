<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can get element handles', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');

    $elementHandles = $buttons->elementHandles();

    expect($elementHandles)->toBeArray();
    expect(count($elementHandles))->toBeGreaterThan(0);

    foreach ($elementHandles as $handle) {
        expect($handle)->toBeInstanceOf(Element::class);
    }
});

it('returns empty array for non-existent elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $nonExistent = $page->locator('.non-existent-class');

    $elementHandles = $nonExistent->elementHandles();

    expect($elementHandles)->toBeArray();
    expect($elementHandles)->toBeEmpty();
});

it('element handles can be interacted with', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');

    $elementHandles = $buttons->elementHandles();

    expect(count($elementHandles))->toBeGreaterThan(0);

    // Test that we can interact with the first element handle
    $firstHandle = $elementHandles[0];
    expect($firstHandle->isVisible())->toBeTrue();
});
