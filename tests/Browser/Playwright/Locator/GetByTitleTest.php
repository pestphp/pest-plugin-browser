<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can create a getByTitle locator from parent locator', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $titleLocator = $parentLocator->getByTitle('Info Button');

    expect($titleLocator)->toBeInstanceOf(Locator::class);
    expect($titleLocator->isVisible())->toBeTrue();
});

it('can find button by title attribute', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('.mb-8');
    $buttonLocator = $containerLocator->getByTitle('Info Button');

    expect($buttonLocator)->toBeInstanceOf(Locator::class);
    expect($buttonLocator->getAttribute('title'))->toBe('Info Button');
});

it('can find link by title attribute', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $linkLocator = $containerLocator->getByTitle('Help Link');

    expect($linkLocator)->toBeInstanceOf(Locator::class);
    expect($linkLocator->getAttribute('title'))->toBe('Help Link');
});

it('can find div by title attribute', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');
    $divLocator = $containerLocator->getByTitle('Important Information');

    expect($divLocator)->toBeInstanceOf(Locator::class);
    expect($divLocator->getAttribute('title'))->toBe('Important Information');
});

it('can use exact title matching', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');
    $exactTitleLocator = $containerLocator->getByTitle('User\'s Name', true);

    expect($exactTitleLocator)->toBeInstanceOf(Locator::class);
    expect($exactTitleLocator->isVisible())->toBeTrue();
});

it('can find title with partial matching', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $partialTitleLocator = $containerLocator->getByTitle('Info');

    expect($partialTitleLocator)->toBeInstanceOf(Locator::class);
    expect($partialTitleLocator->count())->toBeGreaterThan(0);
});

it('can chain getByTitle with other locator methods', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $titleLocator = $profileLocator->getByTitle('User\'s Name');

    expect($titleLocator)->toBeInstanceOf(Locator::class);
    expect($titleLocator->textContent())->toContain('Jane Doe');
});

it('preserves frame context with getByTitle', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('section');
    $titleLocator = $parentLocator->getByTitle('Info Button');

    expect($parentLocator->page())->toBe($titleLocator->page());
});

it('returns proper selector format for getByTitle', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $titleLocator = $parentLocator->getByTitle('Help Link');

    expect($titleLocator->selector())->toContain(' >> ');
    expect($titleLocator->selector())->toBeString();
});

it('can interact with elements found by title', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $buttonLocator = $containerLocator->getByTitle('Info Button');

    expect($buttonLocator->isVisible())->toBeTrue();
    $buttonLocator->click();
    expect($buttonLocator)->toBeInstanceOf(Locator::class);
});

it('handles non-existent title gracefully', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $nonExistentLocator = $parentLocator->getByTitle('Non-existent title');

    expect($nonExistentLocator)->toBeInstanceOf(Locator::class);
    expect($nonExistentLocator->count())->toBe(0);
});

it('works with nested element structures', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $nameLocator = $profileLocator->getByTitle('User\'s Name');

    expect($nameLocator)->toBeInstanceOf(Locator::class);
    expect($nameLocator->isVisible())->toBeTrue();
});

it('can distinguish between different title attributes', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');

    $infoLocator = $containerLocator->getByTitle('Info Button');
    $helpLocator = $containerLocator->getByTitle('Help Link');
    $importantLocator = $containerLocator->getByTitle('Important Information');

    expect($infoLocator)->toBeInstanceOf(Locator::class);
    expect($helpLocator)->toBeInstanceOf(Locator::class);
    expect($importantLocator)->toBeInstanceOf(Locator::class);

    expect($infoLocator->selector())->not()->toBe($helpLocator->selector());
    expect($helpLocator->selector())->not()->toBe($importantLocator->selector());
});
