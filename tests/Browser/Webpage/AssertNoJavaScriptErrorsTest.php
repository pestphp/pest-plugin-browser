<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Pest\Browser\ServerManager;

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

it('asserts that there are javascript errors across navigation', function (): void {
    Route::get('/first', fn (): string => '
        <script>
            wqd,s;
        </script>
        <div>first</div>
    ');

    Route::get('/second', fn (): string => '
        <div>second</div>
    ');

    $page = visit('/first');
    $page->navigate('/second');

    $page->assertNoJavaScriptErrors();
})->throws(ExpectationFailedException::class, 'but found 1: Uncaught ReferenceError: wqd is not define');

it('retains javascript errors across a cross-origin navigation in the same tab', function (): void {
    Route::get('/first', fn (): string => '
        <script>
            wqd,s;
        </script>
        <div>first</div>
    ');

    Route::get('/second', fn (): string => '
        <div>second</div>
    ');

    $page = visit('/first');

    // Same server, different origin (127.0.0.1 -> localhost). Origin-scoped
    // sessionStorage is cleared on the origin change, so the recorded error must
    // be carried outside it to survive the navigation.
    $parts = parse_url(ServerManager::instance()->http()->rewrite('/second'));
    $alternateHost = $parts['host'] === 'localhost' ? '127.0.0.1' : 'localhost';
    $crossOrigin = sprintf('http://%s:%d%s', $alternateHost, $parts['port'], $parts['path']);

    $page->navigate($crossOrigin);

    $page->assertNoJavaScriptErrors();
})->throws(ExpectationFailedException::class, 'but found 1: Uncaught ReferenceError: wqd is not define');
