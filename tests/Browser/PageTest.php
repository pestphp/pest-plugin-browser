<?php

declare(strict_types=1);

it('waits for a function to return true', function (): void {
    Route::get('/', fn (): string => '<script>setTimeout(() => window.ready = true, 500)</script>');

    $page = visit('/')->page();

    $page->waitForFunction('window.ready === true');

    expect($page->evaluate('window.ready'))->toBeTrue();
});

it('waits for a function to return true given an argument', function (): void {
    Route::get('/', fn (): string => '<script>setTimeout(() => window.count = 3, 500)</script>');

    $page = visit('/')->page();

    $page->waitForFunction('count => window.count === count', 3);

    expect($page->evaluate('window.count'))->toBe(3);
});
