<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can create a getByAltText locator from parent locator', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $altTextLocator = $parentLocator->getByAltText('Pest Logo');

    expect($altTextLocator)->toBeInstanceOf(Locator::class);
    expect($altTextLocator->isVisible())->toBeTrue();
});

it('can find image by alt text', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('.mb-8');
    $imageLocator = $containerLocator->getByAltText('Pest Logo');

    expect($imageLocator)->toBeInstanceOf(Locator::class);
    expect($imageLocator->getAttribute('alt'))->toBe('Pest Logo');
});

it('can find different images by different alt text', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $logoLocator = $containerLocator->getByAltText('Pest Logo');
    $anotherImageLocator = $containerLocator->getByAltText('Another Image');

    expect($logoLocator)->toBeInstanceOf(Locator::class);
    expect($anotherImageLocator)->toBeInstanceOf(Locator::class);
    expect($logoLocator->selector())->not()->toBe($anotherImageLocator->selector());
});

it('can use exact alt text matching', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');
    $exactAltLocator = $containerLocator->getByAltText('Profile Picture', true);

    expect($exactAltLocator)->toBeInstanceOf(Locator::class);
    expect($exactAltLocator->isVisible())->toBeTrue();
});

it('can find alt text with partial matching', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $partialAltLocator = $containerLocator->getByAltText('Profile');

    expect($partialAltLocator)->toBeInstanceOf(Locator::class);
    expect($partialAltLocator->isVisible())->toBeTrue();
});

it('can chain getByAltText with other locator methods', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $imageLocator = $profileLocator->getByAltText('Profile Picture');

    expect($imageLocator)->toBeInstanceOf(Locator::class);
    expect($imageLocator->getAttribute('alt'))->toBe('Profile Picture');
});

it('preserves frame context with getByAltText', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('section');
    $altTextLocator = $parentLocator->getByAltText('Pest Logo');

    expect($parentLocator->page())->toBe($altTextLocator->page());
});

it('returns proper selector format for getByAltText', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $altTextLocator = $parentLocator->getByAltText('Pest Logo');

    expect($altTextLocator->selector())->toContain(' >> ');
    expect($altTextLocator->selector())->toBeString();
});

it('can interact with images found by alt text', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $imageLocator = $containerLocator->getByAltText('Pest Logo');

    expect($imageLocator->isVisible())->toBeTrue();
    $imageLocator->click();
    expect($imageLocator)->toBeInstanceOf(Locator::class);
});

it('handles non-existent alt text gracefully', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $nonExistentLocator = $parentLocator->getByAltText('Non-existent alt text');

    expect($nonExistentLocator)->toBeInstanceOf(Locator::class);
    expect($nonExistentLocator->count())->toBe(0);
});

it('works with nested image structures', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $imageLocator = $profileLocator->getByAltText('Profile Picture');

    expect($imageLocator)->toBeInstanceOf(Locator::class);
    expect($imageLocator->isVisible())->toBeTrue();
});

it('can find multiple images in different containers', function (): void {
    $page = page()->goto('/test/selector-tests');
    $bodyLocator = $page->locator('body');
    $allImagesLocator = $bodyLocator->getByAltText('Pest Logo');

    expect($allImagesLocator)->toBeInstanceOf(Locator::class);
    // Should find at least one image with this alt text
    expect($allImagesLocator->count())->toBeGreaterThanOrEqual(1);
});
