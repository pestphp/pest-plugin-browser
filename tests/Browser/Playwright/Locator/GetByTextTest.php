<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can create a getByText locator from parent locator', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $textLocator = $parentLocator->getByText('This is a simple paragraph');

    expect($textLocator)->toBeInstanceOf(Locator::class);
    expect($textLocator->isVisible())->toBeTrue();
});

it('can find text within specific containers', function (): void {
    $page = page()->goto('/test/selector-tests');
    $sectionLocator = $page->locator('.mb-8');
    $textLocator = $sectionLocator->getByText('Click Me Button');

    expect($textLocator)->toBeInstanceOf(Locator::class);
    expect($textLocator->isVisible())->toBeTrue();
});

it('can use exact text matching', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $exactTextLocator = $containerLocator->getByText('This is a special span element', true);

    expect($exactTextLocator)->toBeInstanceOf(Locator::class);
    expect($exactTextLocator->isVisible())->toBeTrue();
});

it('can find partial text without exact matching', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $partialTextLocator = $containerLocator->getByText('special span');

    expect($partialTextLocator)->toBeInstanceOf(Locator::class);
    expect($partialTextLocator->isVisible())->toBeTrue();
});

it('can chain getByText with other locator methods', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $textLocator = $profileLocator->getByText('Jane Doe');

    expect($textLocator)->toBeInstanceOf(Locator::class);
    expect($textLocator->isVisible())->toBeTrue();
});

it('preserves frame context with getByText', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('section');
    $textLocator = $parentLocator->getByText('User Profile');

    expect($parentLocator->page())->toBe($textLocator->page());
});

it('returns proper selector format for getByText', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $textLocator = $parentLocator->getByText('Info');

    expect($textLocator->selector())->toContain(' >> ');
    expect($textLocator->selector())->toContain('text=');
});

it('can interact with buttons found by text', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $buttonLocator = $containerLocator->getByText('Click Me Button');

    expect($buttonLocator->isEnabled())->toBeTrue();
    $buttonLocator->click();
    expect($buttonLocator)->toBeInstanceOf(Locator::class);
});

it('handles non-existent text gracefully', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $nonExistentLocator = $parentLocator->getByText('Non-existent text content');

    expect($nonExistentLocator)->toBeInstanceOf(Locator::class);
    expect($nonExistentLocator->count())->toBe(0);
});

it('can find text in nested elements', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $nameLocator = $profileLocator->getByText('Jane Doe');

    expect($nameLocator)->toBeInstanceOf(Locator::class);
    expect($nameLocator->isVisible())->toBeTrue();
});

it('works with text containing special characters', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');
    $emailLocator = $containerLocator->getByText('jane.doe@example.com');

    expect($emailLocator)->toBeInstanceOf(Locator::class);
    expect($emailLocator->isVisible())->toBeTrue();
});
