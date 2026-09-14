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

it('reports a javascript error that arrives in an unexpected shape', function (): void {
    Route::get('/', fn (): string => '
        <div></div>
    ');

    $page = visit('/');

    // The plugin pushes `{message, filename, lineno, colno}` objects, but the assertion used to
    // build its failure message with `array_map(fn (array $log) => $log['message'], …)` *before*
    // the expectation ran, so a single entry of another shape threw a `TypeError` from inside
    // the assertion instead of reporting the error.
    $page->script('window.__pestBrowser.jsErrors.push("a raw string, not an object"); true');

    $page->assertNoJavaScriptErrors();
})->throws(ExpectationFailedException::class, 'but found 1: a raw string, not an object');
