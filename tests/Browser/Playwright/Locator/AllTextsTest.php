<?php

declare(strict_types=1);

it('can get all inner texts from multiple elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $allInnerTexts = $buttons->allInnerTexts();

    expect($allInnerTexts)->toBeArray();
    expect(count($allInnerTexts))->toBeGreaterThan(0);

    foreach ($allInnerTexts as $text) {
        expect($text)->toBeString();
    }
});

it('returns empty array for non-existent elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $nonExistent = $page->locator('.non-existent-class');
    $allTexts = $nonExistent->allInnerTexts();

    expect($allTexts)->toBeArray();
    expect($allTexts)->toBeEmpty();
});

it('can get all text contents from multiple elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $allTextContents = $buttons->allTextContents();

    expect($allTextContents)->toBeArray();
    expect(count($allTextContents))->toBeGreaterThan(0);

    foreach ($allTextContents as $text) {
        expect($text)->toBeString();
    }
});

it('inner texts and text contents return same count', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');

    $innerTexts = $buttons->allInnerTexts();
    $textContents = $buttons->allTextContents();

    expect(count($innerTexts))->toBe(count($textContents));
});
