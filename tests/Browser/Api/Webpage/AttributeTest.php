<?php

declare(strict_types=1);

it('may get an attribute of an element', function (): void {
    Route::get('/', fn (): string => '<a id="link" href="/about" title="About Us">About</a>');

    $page = visit('/');

    $href = $page->attribute('#link', 'href');
    $title = $page->attribute('#link', 'title');

    expect($href)->toBe('/about');
    expect($title)->toBe('About Us');
});

it('may return null when the attribute does not exist', function (): void {
    Route::get('/', fn (): string => '<a id="link" href="/about">About</a>');

    $page = visit('/');

    $title = $page->attribute('#link', 'title');

    expect($title)->toBeNull();
});
