<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('finds element with querySelector', function (): void {
    $page = page('/test/frame-tests');
    $page->waitForSelector('#test-input');

    $element = $page->querySelector('#test-input');
    expect($element)->toBeInstanceOf(Element::class);
});

it('returns null when element not found with querySelector', function (): void {
    $page = page('/test/frame-tests');

    $element = $page->querySelector('#non-existent-element');
    expect($element)->toBeNull();
});
