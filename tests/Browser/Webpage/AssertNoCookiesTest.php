<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert there are no cookies', function (): void {
    Route::get('/', fn (): string => '<h1>Page with no cookies</h1>');

    $page = visit('/');

    $page->assertNoCookies();
});

it('may fail when asserting no cookies but there are cookies', function (): void {
    Route::get('/', fn (): string => '
        <script>
            document.cookie = "test=1";
            document.cookie = "test2=2";
        </script>
    ');

    $page = visit('/');

    $page->assertNoCookies();
})->throws(ExpectationFailedException::class, 'but found 2');
