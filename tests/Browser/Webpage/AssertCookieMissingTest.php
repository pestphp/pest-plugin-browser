<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert cookie is missing', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertCookieMissing('nonexistent');
});

it('may assert cookie is missing with value', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertCookieMissing('test', 5);
});

it('may fail when asserting cookie is missing but it exists', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertCookieMissing('test');
})->throws(ExpectationFailedException::class, 'Expected cookie');

it('may fail when asserting cookie is missing with value but it exists with that value', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertCookieMissing('test', 1);
})->throws(ExpectationFailedException::class, 'Expected cookie');
