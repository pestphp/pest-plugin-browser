<?php

declare(strict_types=1);

it('may return the current URL', function (): void {
    Route::get('/link', fn (): string => '<a href="/test-url">Test URL</a>');
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/link');

    $page->click('Test URL');

    $page->assertpathIs('/test-url');

    $currentUrl = $page->url();

    expect($currentUrl)->toBe(url('/test-url'));
});
