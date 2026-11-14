<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('asserts that there are no console logs', function (): void {
    Route::get('/', fn (): string => '
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleLogs();
});

it('asserts that there are console logs', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.log("Hello, World!");
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleLogs();
})->throws(ExpectationFailedException::class, 'but found 1: Hello, World!');

it('asserts that there are console logs across navigation', function (): void {
    Route::get('/first', fn (): string => '
        <script>
            console.log("Hello from first page");
        </script>
        <div>first</div>
    ');

    Route::get('/second', fn (): string => '
        <div>second</div>
    ');

    $page = visit('/first');
    $page->navigate('/second');

    $page->assertNoConsoleLogs();
})->throws(ExpectationFailedException::class, 'but found 1: Hello from first page');

