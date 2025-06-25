<?php

declare(strict_types=1);

it('may append text to an input field', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text" value="John">');

    $page = visit('/');

    $page->append('name', ' Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('may append text to an empty input field', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text">');

    $page = visit('/');

    $page->append('#name', 'John Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('may append text to a textarea', function (): void {
    Route::get('/', fn (): string => '<textarea id="message" name="message">Hello</textarea>');

    $page = visit('/');

    $page->append('#message', ' World');

    expect($page->value('#message'))->toBe('Hello World');
});
