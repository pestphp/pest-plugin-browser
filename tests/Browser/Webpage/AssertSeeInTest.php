<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;
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

it('may assert text is in a selector when contains multiple', function (): void {
    Route::get('/', fn (): string => '<div data-test="content"><span>Hello</span><span>Hello</span><span>Hello</span></div>');

    $page = visit('/');

    $page->assertSeeIn('@content', 'Hello');
});

it('counts the assertion when asserting text is in a selector fails', function (): void {
    Route::get('/', fn (): string => '<div data-test="content"><span hidden>Hello</span><span hidden>Hello</span></div>');

    $page = visit('/');

    $count = Assert::getCount();

    try {
        $page->assertSeeIn('@content', 'Hello');
    } catch (ExpectationFailedException) {
        //
    }

    expect(Assert::getCount())->toBe($count + 1);
});
