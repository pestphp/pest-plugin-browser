<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

it('may see text on a page', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->assertSee('Hello World');
});

it('may not see text on a page', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->assertSee('Hello Universe');
})->throws(ExpectationFailedException::class);

it('counts a failed check as an assertion', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $count = Assert::getCount();

    try {
        $page->assertSee('Hello Universe');
    } catch (ExpectationFailedException) {
        //
    }

    expect(Assert::getCount())->toBe($count + 1);
});
