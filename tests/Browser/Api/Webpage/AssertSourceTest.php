<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert source code is present on the page', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertSourceHas('<div id="content">Hello World</div>');
});

it('may fail when asserting source code is present but it is not', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertSourceHas('<div id="content">Hello Universe</div>');
})->throws(ExpectationFailedException::class);

it('may assert source code is not present on the page', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertSourceMissing('<div id="content">Hello Universe</div>');
});

it('may fail when asserting source code is not present but it is', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertSourceMissing('<div id="content">Hello World</div>');
})->throws(ExpectationFailedException::class);
