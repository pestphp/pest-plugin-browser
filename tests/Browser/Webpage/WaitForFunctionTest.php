<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('waits for a function to return a truthy value', function (): void {
    Route::get('/', fn (): string => '<script>setTimeout(() => { window.ready = true; }, 500);</script>Loading');

    $page = visit('/');

    $page->page()->waitForFunction('() => window.ready === true');

    expect($page->script('window.ready'))->toBeTrue();
});

it('waits for an expression to become truthy', function (): void {
    Route::get('/', fn (): string => '<script>setTimeout(() => { document.title = "Ready"; }, 500);</script>Loading');

    $page = visit('/');

    $page->page()->waitForFunction('document.title === "Ready"');

    expect($page->page()->title())->toBe('Ready');
});

it('passes the argument to the function', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->page()->waitForFunction('(expected) => document.body.textContent === expected', 'Hello World');

    expect($page->script('document.body.textContent'))->toBe('Hello World');
});

it('fails when the function never returns a truthy value', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->page()->waitForFunction('() => false');
})->throws(ExpectationFailedException::class);
