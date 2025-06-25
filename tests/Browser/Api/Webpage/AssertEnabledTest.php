<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert field is enabled', function (): void {
    Route::get('/', fn (): string => '
        <input name="name">
        <textarea name="description"></textarea>
        <select name="category"></select>
    ');

    $page = visit('/');

    $page->assertEnabled('name');
    $page->assertEnabled('description');
    $page->assertEnabled('category');
});

it('may fail when asserting field is enabled but it is disabled', function (): void {
    Route::get('/', fn (): string => '<input name="name" disabled>');

    $page = visit('/');

    $page->assertEnabled('name');
})->throws(ExpectationFailedException::class);

it('may assert field is disabled', function (): void {
    Route::get('/', fn (): string => '
        <input name="name" disabled>
        <textarea name="description" disabled></textarea>
        <select name="category" disabled></select>
    ');

    $page = visit('/');

    $page->assertDisabled('name');
    $page->assertDisabled('description');
    $page->assertDisabled('category');
});

it('may fail when asserting field is disabled but it is enabled', function (): void {
    Route::get('/', fn (): string => '<input name="name">');

    $page = visit('/');

    $page->assertDisabled('name');
})->throws(ExpectationFailedException::class);

it('may assert button is enabled', function (): void {
    Route::get('/', fn (): string => '
        <button>Submit</button>
        <input type="submit" value="Save">
        <input type="button" value="Cancel">
    ');

    $page = visit('/');

    $page->assertButtonEnabled('Submit');
    $page->assertButtonEnabled('Save');
    $page->assertButtonEnabled('Cancel');
});

it('may fail when asserting button is enabled but it is disabled', function (): void {
    Route::get('/', fn (): string => '<button disabled>Submit</button>');

    $page = visit('/');

    $page->assertButtonEnabled('Submit');
})->throws(ExpectationFailedException::class);

it('may assert button is disabled', function (): void {
    Route::get('/', fn (): string => '
        <button disabled>Submit</button>
        <input type="submit" value="Save" disabled>
        <input type="button" value="Cancel" disabled>
    ');

    $page = visit('/');

    $page->assertButtonDisabled('Submit');
    $page->assertButtonDisabled('Save');
    $page->assertButtonDisabled('Cancel');
});

it('may fail when asserting button is disabled but it is enabled', function (): void {
    Route::get('/', fn (): string => '<button>Submit</button>');

    $page = visit('/');

    $page->assertButtonDisabled('Submit');
})->throws(ExpectationFailedException::class);
