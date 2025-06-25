<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert dropdown has a specific value selected', function (): void {
    Route::get('/', fn (): string => '
        <select name="country" id="country">
            <option value="us">United States</option>
            <option value="ca" selected>Canada</option>
            <option value="mx">Mexico</option>
        </select>
    ');

    $page = visit('/');

    $page->assertSelected('country', 'ca');
});

it('may fail when asserting dropdown has a specific value selected but it does not', function (): void {
    Route::get('/', fn (): string => '
        <select name="country" id="country">
            <option value="us">United States</option>
            <option value="ca" selected>Canada</option>
            <option value="mx">Mexico</option>
        </select>
    ');

    $page = visit('/');

    $page->assertSelected('country', 'us');
})->throws(ExpectationFailedException::class);

it('may assert dropdown does not have a specific value selected', function (): void {
    Route::get('/', fn (): string => '
        <select name="country" id="country">
            <option value="us">United States</option>
            <option value="ca" selected>Canada</option>
            <option value="mx">Mexico</option>
        </select>
    ');

    $page = visit('/');

    $page->assertNotSelected('country', 'us');
    $page->assertNotSelected('country', 'mx');
});

it('may fail when asserting dropdown does not have a specific value selected but it does', function (): void {
    Route::get('/', fn (): string => '
        <select name="country" id="country">
            <option value="us">United States</option>
            <option value="ca" selected>Canada</option>
            <option value="mx">Mexico</option>
        </select>
    ');

    $page = visit('/');

    $page->assertNotSelected('country', 'ca');
})->throws(ExpectationFailedException::class);
