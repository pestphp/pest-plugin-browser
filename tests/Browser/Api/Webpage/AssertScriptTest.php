<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert javascript expression evaluates to true', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertScript("document.querySelector('#content').textContent === 'Hello World'");
});

it('may assert javascript expression evaluates to specific value', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertScript("document.querySelector('#content').textContent", 'Hello World');
});

it('may fail when javascript expression evaluates to false', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertScript("document.querySelector('#content').textContent === 'Hello Universe'");
})->throws(ExpectationFailedException::class);

it('may fail when javascript expression does not evaluate to expected value', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertScript("document.querySelector('#content').textContent", 'Hello Universe');
})->throws(ExpectationFailedException::class);

it('may assert javascript expression evaluates to integer value', function (): void {
    Route::get('/', fn (): string => '<div id="content" data-count="42">Hello World</div>');

    $page = visit('/');

    $page->assertScript("parseInt(document.querySelector('#content').getAttribute('data-count'))", 42);
});

it('may assert javascript expression evaluates to float value', function (): void {
    Route::get('/', fn (): string => '<div id="content" data-value="3.14">Hello World</div>');

    $page = visit('/');

    $page->assertScript("parseFloat(document.querySelector('#content').getAttribute('data-value'))", 3.14);
});

it('may assert javascript expression evaluates to null', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertScript("document.querySelector('#non-existent')", null);
});

it('may assert javascript expression evaluates to array', function (): void {
    Route::get('/', fn (): string => '
        <ul id="list">
            <li>Item 1</li>
            <li>Item 2</li>
            <li>Item 3</li>
        </ul>
    ');

    $page = visit('/');

    $page->assertScript("
        Array.from(document.querySelectorAll('#list li')).map(el => el.textContent)
    ", ['Item 1', 'Item 2', 'Item 3']);
});
