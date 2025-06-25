<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert URL fragment matches expected fragment', function (): void {
    Route::get('/test-fragment', fn (): string => '
        <div id="content">Test Fragment Page</div>
        <script>
            window.location.hash = "section1";
        </script>
    ');

    $page = visit('/test-fragment');

    $page->assertFragmentIs('section1');
});

it('may fail when asserting URL fragment matches but it does not', function (): void {
    Route::get('/test-fragment', fn (): string => '
        <div id="content">Test Fragment Page</div>
        <script>
            window.location.hash = "section1";
        </script>
    ');

    $page = visit('/test-fragment');

    $page->assertFragmentIs('wrong-section');
})->throws(ExpectationFailedException::class);

it('may assert URL fragment begins with expected string', function (): void {
    Route::get('/test-fragment', fn (): string => '
        <div id="content">Test Fragment Page</div>
        <script>
            window.location.hash = "section1-subsection";
        </script>
    ');

    $page = visit('/test-fragment');

    $page->assertFragmentBeginsWith('section1');
});

it('may fail when asserting URL fragment begins with expected string but it does not', function (): void {
    Route::get('/test-fragment', fn (): string => '
        <div id="content">Test Fragment Page</div>
        <script>
            window.location.hash = "section1-subsection";
        </script>
    ');

    $page = visit('/test-fragment');

    $page->assertFragmentBeginsWith('wrong');
})->throws(ExpectationFailedException::class);

it('may assert URL fragment does not match expected fragment', function (): void {
    Route::get('/test-fragment', fn (): string => '
        <div id="content">Test Fragment Page</div>
        <script>
            window.location.hash = "section1";
        </script>
    ');

    $page = visit('/test-fragment');

    $page->assertFragmentIsNot('wrong-section');
});

it('may fail when asserting URL fragment does not match but it does', function (): void {
    Route::get('/test-fragment', fn (): string => '
        <div id="content">Test Fragment Page</div>
        <script>
            window.location.hash = "section1";
        </script>
    ');

    $page = visit('/test-fragment');

    $page->assertFragmentIsNot('section1');
})->throws(ExpectationFailedException::class);
