<?php

declare(strict_types=1);

it('gets attribute value from element', function (): void {
    $page = page('/test/frame-tests');
    $page->waitForSelector('#test-input');

    $placeholder = $page->locator('#test-input')->getAttribute('placeholder');
    expect($placeholder)->toBe('Enter text here');

    $id = $page->locator('#test-input')->getAttribute('id');
    expect($id)->toBe('test-input');
});

it('returns null for non-existent attribute', function (): void {
    $page = page('/test/frame-tests');
    $page->waitForSelector('#test-input');

    $nonExistent = $page->locator('#test-input')->getAttribute('non-existent-attr');
    expect($nonExistent)->toBeNull();
});
