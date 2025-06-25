<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can create a getByTestId locator from parent locator', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $testIdLocator = $parentLocator->getByTestId('profile-section');

    expect($testIdLocator)->toBeInstanceOf(Locator::class);
    expect($testIdLocator->isVisible())->toBeTrue();
});

it('can chain getByTestId with other locators', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $emailLocator = $profileLocator->getByTestId('user-email');

    expect($emailLocator)->toBeInstanceOf(Locator::class);
    expect($emailLocator->textContent())->toBe('jane.doe@example.com');
});

it('can combine getByTestId with other getBy methods', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $buttonLocator = $profileLocator->getByRole('button');

    expect($buttonLocator)->toBeInstanceOf(Locator::class);
    expect($buttonLocator->isVisible())->toBeTrue();
});

it('can use getByTestId within a filtered locator', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $filteredLocator = $containerLocator->filter(['hasText' => 'User Profile']);
    $emailLocator = $filteredLocator->getByTestId('user-email');

    expect($emailLocator)->toBeInstanceOf(Locator::class);
    expect($emailLocator->textContent())->toContain('jane.doe');
});

it('preserves frame context with getByTestId', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('.mb-8');
    $testIdLocator = $parentLocator->getByTestId('profile-section');

    expect($parentLocator->page())->toBe($testIdLocator->page());
});

it('returns proper selector format for getByTestId', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $testIdLocator = $parentLocator->getByTestId('submit-button');

    expect($testIdLocator->selector())->toContain(' >> ');
    expect($testIdLocator->selector())->toContain('[data-testid="submit-button"]');
});

it('can interact with elements found by getByTestId', function (): void {
    $page = page()->goto('/test/selector-tests');
    $sectionLocator = $page->getByTestId('profile-section');
    $buttonLocator = $sectionLocator->getByTestId('submit-button');

    expect($buttonLocator->isEnabled())->toBeTrue();
    $buttonLocator->click();
    expect($buttonLocator)->toBeInstanceOf(Locator::class);
});

it('handles non-existent testId gracefully', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $nonExistentLocator = $parentLocator->getByTestId('non-existent-testid');

    expect($nonExistentLocator)->toBeInstanceOf(Locator::class)
        ->and($nonExistentLocator->count())->toBe(0);
});

it('can be used with multiple matching parent elements', function (): void {
    $page = page()->goto('/test/selector-tests');
    $sectionsLocator = $page->locator('.mb-8');
    $profileLocator = $sectionsLocator->getByTestId('profile-section');

    expect($profileLocator)->toBeInstanceOf(Locator::class);
    expect($profileLocator->count())->toBeGreaterThanOrEqual(1);
});

it('works with nested getByTestId calls', function (): void {
    $page = page()->goto('/test/selector-tests');
    $outerLocator = $page->locator('section');
    $profileLocator = $outerLocator->getByTestId('user-profile');
    $emailLocator = $profileLocator->getByTestId('user-email');

    expect($emailLocator)->toBeInstanceOf(Locator::class);
    expect($emailLocator->isVisible())->toBeTrue();
});
