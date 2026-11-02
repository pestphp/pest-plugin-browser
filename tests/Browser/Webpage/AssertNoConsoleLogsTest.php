<?php

declare(strict_types=1);

use Pest\Browser\Api\Webpage;
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

it('ignores other console methods unless a level is given', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.error("boom");
            console.warn("careful");
            console.info("note");
            console.debug("detail");
        </script>
    ');

    visit('/')->assertNoConsoleLogs();
});

it('asserts there are no console messages at the given level', function (string $level, string $message): void {
    Route::get('/', fn (): string => '
        <script>
            console.error("boom");
            console.warn("careful");
            console.info("note");
            console.debug("detail");
        </script>
    ');

    expect(fn (): Webpage => visit('/')->assertNoConsoleLogs($level))
        ->toThrow(ExpectationFailedException::class, $message);
})->with([
    ['error', '[error] boom'],
    ['warn', '[warn] careful'],
    ['warning', '[warn] careful'],
    ['info', '[info] note'],
    ['debug', '[debug] detail'],
]);

it('asserts several console levels at once', function (): void {
    Route::get('/', fn (): string => '
        <script>
            console.error("boom");
            console.warn("careful");
            console.log("Hello, World!");
        </script>
    ');

    visit('/')->assertNoConsoleLogs(['error', 'warn']);
})->throws(ExpectationFailedException::class, 'but found 2: [error] boom, [warn] careful');

it('rejects an unknown console level', function (): void {
    Route::get('/', fn (): string => '<div></div>');

    visit('/')->assertNoConsoleLogs('trace');
})->throws(InvalidArgumentException::class, 'Unknown console level [trace]');
