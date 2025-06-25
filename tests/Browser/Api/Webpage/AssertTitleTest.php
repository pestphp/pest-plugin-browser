<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert page title', function (): void {
    Route::get('/', fn (): string => '<html><head><title>Test Title</title></head><body>Hello World</body></html>');

    $page = visit('/');

    $page->assertTitle('Test Title');
});

it('may fail when asserting wrong page title', function (): void {
    Route::get('/', fn (): string => '<html><head><title>Test Title</title></head><body>Hello World</body></html>');

    $page = visit('/');

    $page->assertTitle('Wrong Title');
})->throws(ExpectationFailedException::class);

it('may assert page title contains text', function (): void {
    Route::get('/', fn (): string => '<html><head><title>Test Title Page</title></head><body>Hello World</body></html>');

    $page = visit('/');

    $page->assertTitleContains('Title');
});

it('may fail when asserting page title contains wrong text', function (): void {
    Route::get('/', fn (): string => '<html><head><title>Test Title Page</title></head><body>Hello World</body></html>');

    $page = visit('/');

    $page->assertTitleContains('Wrong');
})->throws(ExpectationFailedException::class);
