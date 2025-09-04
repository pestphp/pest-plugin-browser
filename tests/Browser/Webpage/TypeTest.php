<?php

declare(strict_types=1);

it('may type text in an input field', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text">');

    $page = visit('/');

    $page->type('#name', 'John Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('may type text in a textarea', function (): void {
    Route::get('/', fn (): string => '<textarea id="message" name="message"></textarea>');

    $page = visit('/');

    $page->type('#message', 'Hello World');

    expect($page->value('#message'))->toBe('Hello World');
});

it('may clear the field before typing', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text" value="Initial Value">');

    $page = visit('/');

    $page->type('#name', 'John Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('may type text slowly in an input field', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text">');

    $page = visit('/');

    $page->typeSlowly('#name', 'John Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('may type text slowly in an input field with a custom delay', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text">');

    $page = visit('/');

    $page->typeSlowly('#name', 'John Doe', 100);

    expect($page->value('#name'))->toBe('John Doe');
});
