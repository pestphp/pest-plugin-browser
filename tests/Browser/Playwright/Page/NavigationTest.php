<?php

declare(strict_types=1);

it('navigates forward in browser history', function (): void {
    $page = page('/test/frame-tests');
    $originalUrl = $page->url();

    $page->goto('/test/blank');
    expect($page->url())->toContain('/test/blank');

    $page->back();
    expect($page->url())->toBe($originalUrl);

    $page->forward();
    expect($page->url())->toContain('/test/blank');
});

it('navigates back in browser history', function (): void {
    $page = page('/test/frame-tests');
    $originalUrl = $page->url();

    $page->goto('/test/blank');
    expect($page->url())->toContain('/test/blank');

    $page->back();
    expect($page->url())->toBe($originalUrl);
});

it('reloads the current page', function (): void {
    $page = page('/test/frame-tests');
    $page->locator('#test-input')->fill('test data');
    expect($page->locator('#test-input')->inputValue())->toBe('test data');

    $page->reload();
    expect($page->locator('#test-input')->inputValue())->toBe('');
});
