<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert checkbox is in indeterminate state', function (): void {
    Route::get('/', fn (): string => '
            <input type="checkbox" name="terms" id="terms">
            <script>
                document.getElementById("terms").indeterminate = true;
            </script>
        ');

    $page = visit('/');

    $page->assertIndeterminate('terms');
});

it('may fail when asserting checkbox is in indeterminate state but it is not', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="terms">');

    $page = visit('/');

    $page->assertIndeterminate('terms');
})->throws(ExpectationFailedException::class);

it('may assert checkbox with specific value is in indeterminate state', function (): void {
    Route::get('/', fn (): string => '
            <input type="checkbox" name="color" value="red" id="color-red">
            <input type="checkbox" name="color" value="blue">
            <script>
                document.getElementById("color-red").indeterminate = true;
            </script>
        ');

    $page = visit('/');

    $page->assertIndeterminate('color', 'red');
});

it('may fail when asserting checkbox with specific value is in indeterminate state but it is not', function (): void {
    Route::get('/', fn (): string => '<input type="checkbox" name="color" value="red"><input type="checkbox" name="color" value="blue">');

    $page = visit('/');

    $page->assertIndeterminate('color', 'red');
})->throws(ExpectationFailedException::class);
