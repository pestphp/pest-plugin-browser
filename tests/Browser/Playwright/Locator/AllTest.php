<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can get all matching locators', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $allButtons = $buttons->all();

    expect($allButtons)->toBeArray();
    expect(count($allButtons))->toBeGreaterThan(0);

    foreach ($allButtons as $button) {
        expect($button)->toBeInstanceOf(Locator::class);
    }
});

it('returns empty array for non-existent elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $nonExistent = $page->locator('.non-existent-class');
    $allElements = $nonExistent->all();

    expect($allElements)->toBeArray();
    expect($allElements)->toBeEmpty();
});

it('can get all text contents from multiple elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $allButtons = $buttons->all();

    expect(count($allButtons))->toBeGreaterThan(1);

    // Verify each element in the array can be interacted with
    foreach ($allButtons as $button) {
        expect($button->isVisible())->toBeTrue();
    }
});
