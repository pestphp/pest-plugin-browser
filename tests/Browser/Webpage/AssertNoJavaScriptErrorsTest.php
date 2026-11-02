<?php

declare(strict_types=1);

use Pest\Browser\Api\Webpage;
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

it('asserts that content security policy violations are javascript errors', function (string $header, string $disposition): void {
    $html = <<<'HTML'
        <!DOCTYPE html>
        <html>
            <body>
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="">
            </body>
        </html>
        HTML;

    Route::get('/', fn () => response($html)->header($header, "img-src 'none'"));

    expect(fn (): Webpage => visit('/')->assertNoJavaScriptErrors())
        ->toThrow(ExpectationFailedException::class, "CSP {$disposition}: img-src blocked");
})->with([
    ['Content-Security-Policy', 'enforce'],
    ['Content-Security-Policy-Report-Only', 'report'],
]);
