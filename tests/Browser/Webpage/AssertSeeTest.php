<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may see text on a page', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->assertSee('Hello World');
});

it('may not see text on a page', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->assertSee('Hello Universe');
})->throws(ExpectationFailedException::class);

it('keeps retrying until text that appears later is seen', function (): void {
    Route::get('/', fn (): string => '
        <span id="late"></span>

        <script>
            setTimeout(function () {
                document.getElementById("late").textContent = "Hello Later";
            }, 1200);
        </script>
    ');

    $page = visit('/');

    $page->assertSee('Hello Later');
});
