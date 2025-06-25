<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert checkbox is checked', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="terms" checked>');

    $page = visit('/');

    $page->assertChecked('terms');
});

it('may fail when asserting checkbox is checked but it is not', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="terms">');

    $page = visit('/');

    $page->assertChecked('terms');
})->throws(ExpectationFailedException::class);

it('may assert checkbox with specific value is checked', function (): void {
    Route::get('/', fn (): string => '
        <input type="checkbox" name="color" value="red" checked>
        <input type="checkbox" name="color" value="blue">
    ');

    $page = visit('/');

    $page->assertChecked('color', 'red');
});

it('may fail when asserting checkbox with specific value is checked but it is not', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="color" value="red" checked><input type="checkbox" name="color" value="blue">');

    $page = visit('/');

    $page->assertChecked('color', 'blue');
})->throws(ExpectationFailedException::class);

it('may assert checkbox is not checked', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="terms">');

    $page = visit('/');

    $page->assertNotChecked('terms');
});

it('may fail when asserting checkbox is not checked but it is', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="terms" checked>');

    $page = visit('/');

    $page->assertNotChecked('terms');
})->throws(ExpectationFailedException::class);

it('may assert checkbox with specific value is not checked', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="color" value="red" checked><input type="checkbox" name="color" value="blue">');

    $page = visit('/');

    $page->assertNotChecked('color', 'blue');
});

it('may fail when asserting checkbox with specific value is not checked but it is', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="color" value="red" checked><input type="checkbox" name="color" value="blue">');

    $page = visit('/');

    $page->assertNotChecked('color', 'red');
})->throws(ExpectationFailedException::class);
