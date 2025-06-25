<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert text is not on a page', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->assertDontSee('Hello Universe');
});

it('may fail when asserting text is not on a page but it is', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->assertDontSee('Hello World');
})->throws(ExpectationFailedException::class);
