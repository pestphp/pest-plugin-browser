<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can create a getByLabel locator from parent locator', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $labelLocator = $parentLocator->getByLabel('Username');

    expect($labelLocator)->toBeInstanceOf(Locator::class);
    expect($labelLocator->isVisible())->toBeTrue();
});

it('can find input by associated label', function (): void {
    $page = page()->goto('/test/selector-tests');
    $formLocator = $page->locator('.mb-8');
    $usernameInput = $formLocator->getByLabel('Username');

    expect($usernameInput)->toBeInstanceOf(Locator::class);
    expect($usernameInput->inputValue())->toBe('johndoe');
});

it('can find password input by label', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('div');
    $passwordInput = $containerLocator->getByLabel('Password');

    expect($passwordInput)->toBeInstanceOf(Locator::class);
    expect($passwordInput->getAttribute('type'))->toBe('password');
});

it('can use exact label matching', function (): void {
    $page = page()->goto('/test/selector-tests');
    $formLocator = $page->locator('body');
    $exactLabelLocator = $formLocator->getByLabel('Username', true);

    expect($exactLabelLocator)->toBeInstanceOf(Locator::class);
    expect($exactLabelLocator->isVisible())->toBeTrue();
});

it('can chain getByLabel with other locator methods', function (): void {
    $page = page()->goto('/test/selector-tests');
    $sectionLocator = $page->locator('.mb-8');
    $labelLocator = $sectionLocator->getByLabel('Username');

    expect($labelLocator)->toBeInstanceOf(Locator::class);
    expect($labelLocator->getAttribute('name'))->toBe('username');
});

it('preserves frame context with getByLabel', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('section');
    $labelLocator = $parentLocator->getByLabel('Password');

    expect($parentLocator->page())->toBe($labelLocator->page());
});

it('returns proper selector format for getByLabel', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $labelLocator = $parentLocator->getByLabel('Username');

    expect($labelLocator->selector())->toContain(' >> ');
    // The exact format depends on the Selector implementation
    expect($labelLocator->selector())->toBeString();
});

it('can interact with inputs found by label', function (): void {
    $page = page()->goto('/test/selector-tests');
    $formLocator = $page->locator('form, div');
    $usernameInput = $formLocator->getByLabel('Username');

    $usernameInput->fill('newuser');
    expect($usernameInput->inputValue())->toBe('newuser');
});

it('handles non-existent label gracefully', function (): void {
    $page = page()->goto('/test/selector-tests');
    $parentLocator = $page->locator('body');
    $nonExistentLocator = $parentLocator->getByLabel('Non-existent Label');

    expect($nonExistentLocator)->toBeInstanceOf(Locator::class);
    expect($nonExistentLocator->count())->toBe(0);
});

it('works with nested form structures', function (): void {
    $page = page()->goto('/test/selector-tests');
    $sectionLocator = $page->locator('.mb-8');
    $labelLocator = $sectionLocator->getByLabel('Password');

    expect($labelLocator)->toBeInstanceOf(Locator::class);
    expect($labelLocator->isVisible())->toBeTrue();
});

it('can find checkbox by label', function (): void {
    $page = page()->goto('/test/selector-tests');
    $containerLocator = $page->locator('body');
    $checkboxLocator = $containerLocator->getByLabel('Remember Me');

    expect($checkboxLocator)->toBeInstanceOf(Locator::class);
    expect($checkboxLocator->getAttribute('type'))->toBe('checkbox');
});
