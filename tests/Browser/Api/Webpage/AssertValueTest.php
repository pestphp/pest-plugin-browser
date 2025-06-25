<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert element has a specific value', function (): void {
    Route::get('/', fn (): string => '
        <input id="name" value="John Doe">
        <textarea id="description">Product description</textarea>
        <select id="category">
            <option value="electronics" selected>Electronics</option>
            <option value="books">Books</option>
        </select>
    ');

    $page = visit('/');

    $page->assertValue('#name', 'John Doe');
    $page->assertValue('#description', 'Product description');
    $page->assertValue('#category', 'electronics');
});

it('may fail when asserting element has a specific value but it does not', function (): void {
    Route::get('/', fn (): string => '<input id="name" value="John Doe">');

    $page = visit('/');

    $page->assertValue('#name', 'Jane Doe');
})->throws(ExpectationFailedException::class);

it('may assert element does not have a specific value', function (): void {
    Route::get('/', fn (): string => '
        <input id="name" value="John Doe">
        <textarea id="description">Product description</textarea>
        <select id="category">
            <option value="electronics" selected>Electronics</option>
            <option value="books">Books</option>
        </select>
    ');

    $page = visit('/');

    $page->assertValueIsNot('#name', 'Jane Doe');
    $page->assertValueIsNot('#description', 'Another description');
    $page->assertValueIsNot('#category', 'books');
});

it('may fail when asserting element does not have a specific value but it does', function (): void {
    Route::get('/', fn (): string => '<input id="name" value="John Doe">');

    $page = visit('/');

    $page->assertValueIsNot('#name', 'John Doe');
})->throws(ExpectationFailedException::class);
