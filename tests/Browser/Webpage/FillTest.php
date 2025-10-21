<?php

declare(strict_types=1);

it('may fill text in an input field', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text">');

    $page = visit('/');

    $page->fill('#name', 'John Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('may fill text in a textarea', function (): void {
    Route::get('/', fn (): string => '<textarea id="message" name="message"></textarea>');

    $page = visit('/');

    $page->fill('#message', 'Hello World');

    expect($page->value('#message'))->toBe('Hello World');
});

it('may clear the field before typing', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text" value="Initial Value">');

    $page = visit('/');

    $page->fill('#name', 'John Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('can fill an input with exact match css selectors', function (string $selector): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="text" name="username" id="username" value="Initial Value">
        </form>
    ');

    $page = visit('/');

    $page->fill($selector, 'New Username');
    expect($page->value('#username'))->toBe('New Username');
})->with([
    '[name]',
    '[name*="username"]',
    '[name^="username"]',
    '[name$="username"]',
    'input[name="username"]',
]);
