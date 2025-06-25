<?php

declare(strict_types=1);

it('may clear an input field', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text" value="John Doe">');

    $page = visit('/');

    $page->clear('#name');

    expect($page->value('#name'))->toBe('');
});

it('may clear a textarea', function (): void {
    Route::get('/', fn (): string => '<textarea id="message" name="message">Hello World</textarea>');

    $page = visit('/');

    $page->clear('#message');

    expect($page->value('#message'))->toBe('');
});

it('may clear a field and then type new text', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text" value="John Doe">');

    $page = visit('/');

    $page->clear('#name')->type('#name', 'Jane Smith');

    expect($page->value('#name'))->toBe('Jane Smith');
});
