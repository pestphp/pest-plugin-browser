<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert any text is in a selector', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div><div id="empty"></div>');

    $page = visit('/');

    $page->assertSeeAnythingIn('#content');
});

it('may fail when asserting any text is in a selector but it is empty', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div><div id="empty"></div>');

    $page = visit('/');

    $page->assertSeeAnythingIn('#empty');
})->throws(ExpectationFailedException::class);

it('may assert no text is in a selector', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div><div id="empty"></div>');

    $page = visit('/');

    $page->assertSeeNothingIn('#empty');
});

it('may fail when asserting no text is in a selector but it has text', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div><div id="empty"></div>');

    $page = visit('/');

    $page->assertSeeNothingIn('#content');
})->throws(ExpectationFailedException::class);
