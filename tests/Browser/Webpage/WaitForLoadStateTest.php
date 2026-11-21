<?php

declare(strict_types=1);

it('waits for the load states', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->page()
        ->waitForLoadState('domcontentloaded')
        ->waitForLoadState('load')
        ->waitForLoadState('networkidle');

    expect($page->script('document.readyState'))->toBe('complete');
});

it('waits for the load event by default', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->page()->waitForLoadState();

    expect($page->script('document.readyState'))->toBe('complete');
});
