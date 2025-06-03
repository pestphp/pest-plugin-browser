<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can filter locators', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $filteredButtons = $buttons->filter('[data-testid="click-button"]');

    expect($filteredButtons)->toBeInstanceOf(Locator::class);
});

it('can filter by text content', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $filteredButtons = $buttons->filter(['hasText' => 'Click']);

    expect($filteredButtons)->toBeInstanceOf(Locator::class);
    expect($filteredButtons->count())->toBeGreaterThan(0);
});

it('can filter by exact text content', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $filteredButtons = $buttons->filter(['hasText' => 'Click Me', 'exact' => true]);

    expect($filteredButtons)->toBeInstanceOf(Locator::class);
});

it('can filter by text that should not be present', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $filteredButtons = $buttons->filter(['hasNotText' => 'Hidden']);

    expect($filteredButtons)->toBeInstanceOf(Locator::class);
});

it('can filter by child locator presence', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containers = $page->locator('.container');
    $filteredContainers = $containers->filter(['has' => $page->locator('button')]);

    expect($filteredContainers)->toBeInstanceOf(Locator::class);
});

it('can filter by child locator absence', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containers = $page->locator('.container');
    $filteredContainers = $containers->filter(['hasNot' => $page->locator('.hidden')]);

    expect($filteredContainers)->toBeInstanceOf(Locator::class);
});

it('can combine multiple filter options', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $elements = $page->locator('*');
    $filteredElements = $elements->filter([
        'hasText' => 'Click',
        'hasNot' => $page->locator('.disabled'),
    ]);

    expect($filteredElements)->toBeInstanceOf(Locator::class);
});

it('backward compatibility with string filter', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');

    // Old string-based filter should still work
    $filteredButtons = $buttons->filter('[data-testid="click-button"]');

    expect($filteredButtons)->toBeInstanceOf(Locator::class);
});

it('can use filterBySelector method', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $filteredButtons = $buttons->filterBySelector('[data-testid="click-button"]');

    expect($filteredButtons)->toBeInstanceOf(Locator::class);
});
