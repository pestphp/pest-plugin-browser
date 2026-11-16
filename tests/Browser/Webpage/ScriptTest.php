<?php

declare(strict_types=1);

it('may run a script on the page', function (): void {
    Route::get('/', fn (): string => '<span id="value">Hello</span>');

    $page = visit('/');

    expect($page->script('() => document.getElementById("value").textContent'))->toBe('Hello');
});

it('runs a script only once when it takes longer than a second but fits the timeout', function (): void {
    Route::get('/', fn (): string => '<script>window.runs = 0;</script>');

    $page = visit('/');

    $page->script('() => {
        window.runs += 1;

        const until = performance.now() + 1500;

        while (performance.now() < until) {}
    }');

    expect($page->script('() => window.runs'))->toBe(1);
});
