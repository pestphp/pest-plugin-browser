<?php

declare(strict_types=1);

it('may get the text of an element', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $text = $page->text('#content');

    expect($text)->toBe('Hello World');
});

it('may get the text of an element with nested elements', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello <span>World</span></div>');

    $page = visit('/');

    $text = $page->text('#content');

    expect($text)->toBe('Hello World');
});
