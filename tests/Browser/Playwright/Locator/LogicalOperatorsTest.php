<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can combine locators with and()', function (): void {
    $page = page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $clickButtons = $page->locator('[data-testid="click-button"]');

    $combined = $buttons->and($clickButtons);

    expect($combined)->toBeInstanceOf(Locator::class)
        ->and($combined->count())->toBeGreaterThan(0);
});

it('can combine locators with or()', function (): void {
    $page = page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $inputs = $page->locator('input');

    $combined = $buttons->or($inputs);

    expect($combined)->toBeInstanceOf(Locator::class);
    expect($combined->count())->toBeGreaterThanOrEqual($buttons->count());
    expect($combined->count())->toBeGreaterThanOrEqual($inputs->count());
});

it('and() returns intersection of locators', function (): void {
    $page = page()->goto('/test/element-tests');
    $allButtons = $page->locator('button');
    $testIdButtons = $page->locator('[data-testid]');

    $intersection = $allButtons->and($testIdButtons);

    expect($intersection->count())->toBeLessThanOrEqual($allButtons->count());
    expect($intersection->count())->toBeLessThanOrEqual($testIdButtons->count());
});

it('or() returns union of locators', function (): void {
    $page = page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $inputs = $page->locator('input');

    $buttonCount = $buttons->count();
    $inputCount = $inputs->count();
    $unionCount = $buttons->or($inputs)->count();

    expect($unionCount)->toBeGreaterThanOrEqual($buttonCount);
    expect($unionCount)->toBeGreaterThanOrEqual($inputCount);
});
