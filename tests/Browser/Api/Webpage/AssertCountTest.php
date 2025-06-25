<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert element count', function (): void {
    Route::get('/', fn (): string => '<div class="item">Item 1</div><div class="item">Item 2</div><div class="item">Item 3</div>');

    $page = visit('/');

    $page->assertCount('.item', 3);
});

it('may fail when asserting incorrect element count', function (): void {
    Route::get('/', fn (): string => '<div class="item">Item 1</div><div class="item">Item 2</div><div class="item">Item 3</div>');

    $page = visit('/');

    $page->assertCount('.item', 2);
})->throws(ExpectationFailedException::class);

it('may assert zero count for non-existent elements', function (): void {
    Route::get('/', fn (): string => '<div class="item">Item 1</div><div class="item">Item 2</div>');

    $page = visit('/');

    $page->assertCount('.non-existent', 0);
});
