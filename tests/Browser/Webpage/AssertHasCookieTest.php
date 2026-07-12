<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert cookie exists', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertHasCookie('test');
});

it('may assert cookie exists with value', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertHasCookie('test', 1);
});

it('may fail when asserting cookie exists but it does not', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertHasCookie('foobar');
})->throws(ExpectationFailedException::class, 'but it was not found');

it('may fail when asserting cookie exists with value but value does not match', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertHasCookie('test', 2);
})->throws(ExpectationFailedException::class, 'but it was not found');
