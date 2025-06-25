<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can create a getByPlaceholder locator from parent locator', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $placeholderLocator = $parentLocator->getByPlaceholder('Search...');

    expect($placeholderLocator)->toBeInstanceOf(Locator::class);
    expect($placeholderLocator->isVisible())->toBeTrue();
});

it('can find input by placeholder text', function (): void {
    $page = page()->goto('/test/selector-tests');
    $formLocator = $page->locator('.mb-8');
    $searchInput = $formLocator->getByPlaceholder('Search...');

    expect($searchInput)->toBeInstanceOf(Locator::class);
    expect($searchInput->getAttribute('type'))->toBe('text');
});

it('can find textarea by placeholder', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $textareaLocator = $containerLocator->getByPlaceholder('Enter your comments here');

    expect($textareaLocator)->toBeInstanceOf(Locator::class);
    expect($textareaLocator->isVisible())->toBeTrue();
});

it('can use exact placeholder matching', function (): void {
    $page = page()->goto('/test/selector-tests');
    $formLocator = $page->locator('body');
    $exactPlaceholderLocator = $formLocator->getByPlaceholder('Search...', true);

    expect($exactPlaceholderLocator)->toBeInstanceOf(Locator::class);
    expect($exactPlaceholderLocator->isVisible())->toBeTrue();
});

it('can find placeholder with partial text', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $partialPlaceholderLocator = $containerLocator->getByPlaceholder('comments');

    expect($partialPlaceholderLocator)->toBeInstanceOf(Locator::class);
    expect($partialPlaceholderLocator->isVisible())->toBeTrue();
});

it('can chain getByPlaceholder with other locator methods', function (): void {
    $page = page()->goto('/test/selector-tests');
    $sectionLocator = $page->locator('.mb-8');
    $placeholderLocator = $sectionLocator->getByPlaceholder('Search...');

    expect($placeholderLocator)->toBeInstanceOf(Locator::class);
    expect($placeholderLocator->getAttribute('placeholder'))->toBe('Search...');
});

it('preserves frame context with getByPlaceholder', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('section');
    $placeholderLocator = $parentLocator->getByPlaceholder('Search...');

    expect($parentLocator->page())->toBe($placeholderLocator->page());
});

it('returns proper selector format for getByPlaceholder', function (): void {
    $page = page()->goto('/test/selector-tests');

    $parentLocator = $page->locator('body');
    $placeholderLocator = $parentLocator->getByPlaceholder('Search...');

    expect($placeholderLocator->selector())->toContain(' >> ')
        ->and($placeholderLocator->selector())->toBeString();
});

it('can interact with inputs found by placeholder', function (): void {
    $page = page()->goto('/test/selector-tests');
    $formLocator = $page->locator('div');
    $searchInput = $formLocator->getByPlaceholder('Search...');

    $searchInput->fill('test search');
    expect($searchInput->inputValue())->toBe('test search');
});

it('handles non-existent placeholder gracefully', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $nonExistentLocator = $parentLocator->getByPlaceholder('Non-existent placeholder');

    expect($nonExistentLocator)->toBeInstanceOf(Locator::class);
    expect($nonExistentLocator->count())->toBe(0);
});

it('works with nested form structures', function (): void {
    $page = page()->goto('/test/selector-tests');
    $sectionLocator = $page->locator('.mb-8');
    $textareaLocator = $sectionLocator->getByPlaceholder('Enter your comments here');

    expect($textareaLocator)->toBeInstanceOf(Locator::class);
    expect($textareaLocator->isVisible())->toBeTrue();
});

it('can distinguish between different placeholder texts', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');

    $searchLocator = $containerLocator->getByPlaceholder('Search...');
    $commentsLocator = $containerLocator->getByPlaceholder('Enter your comments here');

    expect($searchLocator)->toBeInstanceOf(Locator::class)
        ->and($commentsLocator)->toBeInstanceOf(Locator::class)
        ->and($searchLocator->selector())->not()->toBe($commentsLocator->selector());
});
