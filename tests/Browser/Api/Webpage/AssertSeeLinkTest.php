<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert link is present on the page', function (): void {
    Route::get('/', fn (): string => '<a href="/about">About Us</a>');

    $page = visit('/');

    $page->assertSeeLink('About Us');
});

it('may fail when asserting link is present but it is not', function (): void {
    Route::get('/', fn (): string => '<a href="/about">About Us</a>');

    $page = visit('/');

    $page->assertSeeLink('Contact Us');
})->throws(ExpectationFailedException::class);

it('may assert link is not present on the page', function (): void {
    Route::get('/', fn (): string => '<a href="/about">About Us</a>');

    $page = visit('/');

    $page->assertDontSeeLink('Contact Us');
});

it('may fail when asserting link is not present but it is', function (): void {
    Route::get('/', fn (): string => '<a href="/about">About Us</a>');

    $page = visit('/');

    $page->assertDontSeeLink('About Us');
})->throws(ExpectationFailedException::class);
