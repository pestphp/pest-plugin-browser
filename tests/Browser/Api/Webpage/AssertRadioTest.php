<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert radio is selected', function (): void {
    Route::get('/', fn (): string => '<input type="radio" name="color" value="red" checked><input type="radio" name="color" value="blue">');

    $page = visit('/');

    $page->assertRadioSelected('color', 'red');
});

it('may fail when asserting radio is selected but it is not', function (): void {
    Route::get('/', fn (): string => '<input type="radio" name="color" value="red" checked><input type="radio" name="color" value="blue">');

    $page = visit('/');

    $page->assertRadioSelected('color', 'blue');
})->throws(ExpectationFailedException::class);

it('may assert radio is not selected', function (): void {
    Route::get('/', fn (): string => '<input type="radio" name="color" value="red" checked><input type="radio" name="color" value="blue">');

    $page = visit('/');

    $page->assertRadioNotSelected('color', 'blue');
});

it('may fail when asserting radio is not selected but it is', function (): void {
    Route::get('/', fn (): string => '<input type="radio" name="color" value="red" checked><input type="radio" name="color" value="blue">');

    $page = visit('/');

    $page->assertRadioNotSelected('color', 'red');
})->throws(ExpectationFailedException::class);

it('may assert all radios in a group are not selected', function (): void {
    Route::get('/', fn (): string => '<input type="radio" name="size" value="small"><input type="radio" name="size" value="medium"><input type="radio" name="size" value="large">');

    $page = visit('/');

    $page->assertRadioNotSelected('size');
});

it('may fail when asserting all radios in a group are not selected but one is', function (): void {
    Route::get('/', fn (): string => '<input type="radio" name="size" value="small"><input type="radio" name="size" value="medium" checked><input type="radio" name="size" value="large">');

    $page = visit('/');

    $page->assertRadioNotSelected('size');
})->throws(ExpectationFailedException::class);
