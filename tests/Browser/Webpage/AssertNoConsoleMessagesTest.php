<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\ExpectationFailedException;

it('asserts that there are no console messages', function (): void {
    Route::get('/', fn (): string => '
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleMessages();
});

it('ignores debug messages by default', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.debug("Hello, World!");
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleMessages();
});

it('asserts that there are console messages', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.error("Hello, World!");
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleMessages();
})->throws(ExpectationFailedException::class, 'but found 1: error: Hello, World!');

it('asserts that there are console debug messages', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.debug("Hello, World!");
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleMessages('debug');
})->throws(ExpectationFailedException::class, 'but found 1: debug: Hello, World!');

it('asserts that there are console error messages', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.error("Hello, World!");
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleMessages('error');
})->throws(ExpectationFailedException::class, 'but found 1: error: Hello, World!');

it('asserts that there are console info messages', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.info("Hello, World!");
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleMessages('info');
})->throws(ExpectationFailedException::class, 'but found 1: info: Hello, World!');

it('asserts that there are console warning messages', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.warn("Hello, World!");
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleMessages('warning');
})->throws(ExpectationFailedException::class, 'but found 1: warning: Hello, World!');

it('allows alias warn for console warning messages', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.warn("Hello, World!");
        </script>
        <div></div>
    ');

    $page = visit('/');

    $page->assertNoConsoleMessages('warn');
})->throws(ExpectationFailedException::class, 'but found 1: warning: Hello, World!');
