<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Pest\Browser\ServerManager;

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

it('retains console logs across a cross-origin navigation in the same tab', function (): void {
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

    // Same server, different origin (127.0.0.1 -> localhost). Origin-scoped
    // sessionStorage is cleared on the origin change, so the recorded log must
    // be carried outside it to survive the navigation.
    $parts = parse_url(ServerManager::instance()->http()->rewrite('/second'));
    $alternateHost = $parts['host'] === 'localhost' ? '127.0.0.1' : 'localhost';
    $crossOrigin = sprintf('http://%s:%d%s', $alternateHost, $parts['port'], $parts['path']);

    $page->navigate($crossOrigin);

    $page->assertNoConsoleLogs();
})->throws(ExpectationFailedException::class, 'but found 1: Hello from first page');
