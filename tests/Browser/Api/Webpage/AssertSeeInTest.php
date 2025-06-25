<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert text is in a selector', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertSeeIn('#content', 'Hello World');
});

it('may fail when asserting text is in a selector but it is not', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertSeeIn('#content', 'Hello Universe');
})->throws(ExpectationFailedException::class);

it('may assert text is not in a selector', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertDontSeeIn('#content', 'Hello Universe');
});

it('may fail when asserting text is not in a selector but it is', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertDontSeeIn('#content', 'Hello World');
})->throws(ExpectationFailedException::class);
