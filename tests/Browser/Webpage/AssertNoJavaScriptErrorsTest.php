<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('asserts that there are no javascript errors', function (): void {
    Route::get('/', fn (): string => '
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoJavaScriptErrors();
});

it('asserts that there are javascript errors', function (): void {
    Route::get('/', fn (): string => '
        <script>
            wqd,s;
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoJavaScriptErrors();
})->throws(ExpectationFailedException::class, 'but found 1: Uncaught ReferenceError: wqd is not define');

it('asserts that there are unhandled promise rejection errors', function (): void {
    Route::get('/', fn (): string => '
        <script>
            Promise.reject(new Error("Unhandled promise rejection"));
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoJavaScriptErrors();
})->throws(ExpectationFailedException::class, 'but found 1: Unhandled promise rejection');
