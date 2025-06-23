<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can create a getByRole locator from parent locator', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $roleLocator = $parentLocator->getByRole('button');

    expect($roleLocator)->toBeInstanceOf(Locator::class);
    expect($roleLocator->count())->toBeGreaterThan(0);
});

it('can find button by role with name parameter', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $buttonLocator = $containerLocator->getByRole('button', ['name' => 'Save']);

    expect($buttonLocator)->toBeInstanceOf(Locator::class);
    expect($buttonLocator->isVisible())->toBeTrue();
});

it('can find checkbox by role', function (): void {
    $page = page()->goto('/test/selector-tests');
    $formLocator = $page->locator('div');
    $checkboxLocator = $formLocator->getByRole('checkbox');

    expect($checkboxLocator)->toBeInstanceOf(Locator::class);
    expect($checkboxLocator->isVisible())->toBeTrue();
});

it('can find link by role', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');
    $linkLocator = $containerLocator->getByRole('link');

    expect($linkLocator)->toBeInstanceOf(Locator::class);
    expect($linkLocator->count())->toBeGreaterThan(0);
});

it('can chain getByRole with other locator methods', function (): void {
    $page = page()->goto('/test/selector-tests');
    $profileLocator = $page->getByTestId('user-profile');
    $buttonLocator = $profileLocator->getByRole('button');

    expect($buttonLocator)->toBeInstanceOf(Locator::class);
    expect($buttonLocator->isVisible())->toBeTrue();
});

it('preserves frame context with getByRole', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('section');
    $roleLocator = $parentLocator->getByRole('button');

    expect($parentLocator->page())->toBe($roleLocator->page());
});

it('returns proper selector format for getByRole', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $roleLocator = $parentLocator->getByRole('button');

    expect($roleLocator->selector())->toContain(' >> ');
    expect($roleLocator->selector())->toContain('role=button');
});

it('can interact with elements found by role', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('.mb-8');
    $buttonLocator = $containerLocator->getByRole('button', ['name' => 'Save']);

    expect($buttonLocator->isEnabled())->toBeTrue();
    $buttonLocator->click();
    expect($buttonLocator)->toBeInstanceOf(Locator::class);
});

it('handles non-existent role gracefully', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $nonExistentLocator = $parentLocator->getByRole('menuitem');

    expect($nonExistentLocator)->toBeInstanceOf(Locator::class);
    expect($nonExistentLocator->count())->toBe(0);
});

it('can find multiple buttons with same role', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('.mb-8');
    $buttonsLocator = $containerLocator->getByRole('button');

    expect($buttonsLocator)->toBeInstanceOf(Locator::class)
        ->and($buttonsLocator->count())->toBeGreaterThan(1);
});

it('works with role parameters for specificity', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');
    $specificButtonLocator = $containerLocator->getByRole('button', ['name' => 'Edit Profile']);

    expect($specificButtonLocator)->toBeInstanceOf(Locator::class);
    expect($specificButtonLocator->isVisible())->toBeTrue();
});
