<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Playwright;

// Each slow response takes longer than one attempt of the expectation retry loop (one second)
// and leaves headroom within the timeout configured here.
beforeEach(function (): void {
    $this->previousTimeout = Playwright::timeout();

    Playwright::setTimeout(5_000);
});

afterEach(function (): void {
    Playwright::setTimeout($this->previousTimeout);
});

it('navigates only once when the page takes longer than an attempt to load', function (): void {
    $loads = 0;

    Route::get('/page-a', fn (): string => '<p id="page">page 1</p>');
    Route::get('/page-b', function () use (&$loads): string {
        $loads++;

        usleep(1_500_000);

        return '<p id="page">page 2</p>';
    });

    $page = visit('/page-a');

    $page->navigate('/page-b');

    expect($loads)->toBe(1)
        ->and($page->text('#page'))->toBe('page 2');
});

it('refreshes only once when the page takes longer than an attempt to load', function (): void {
    $loads = 0;

    Route::get('/page', function () use (&$loads): string {
        $loads++;

        if ($loads > 1) {
            usleep(1_500_000);
        }

        return "<p id=\"page\">Page {$loads}</p>";
    });

    $page = visit('/page');

    $page->refresh();

    expect($loads)->toBe(2)
        ->and($page->text('#page'))->toBe('Page 2');
});

it('goes back and forward only once when the page takes longer than an attempt to load', function (): void {
    $loads = ['/page-a' => 0, '/page-b' => 0];

    foreach (array_keys($loads) as $path) {
        Route::get($path, function () use (&$loads, $path) {
            $loads[$path]++;

            if ($loads[$path] > 1) {
                usleep(1_500_000);
            }

            // no-store prevents HTTP-cache reuse; Playwright's default Chromium launch disables the back/forward cache.
            return response("<p id=\"page\">{$path}</p>")->header('Cache-Control', 'no-store');
        });
    }

    Route::get('/page-c', fn (): string => '<p id="page">/page-c</p>');

    $page = visit('/page-a');
    $page->navigate('/page-b');
    $page->navigate('/page-c');

    $page->back();

    expect($page->text('#page'))->toBe('/page-b')
        ->and($loads['/page-b'])->toBe(2);

    $page->back();

    expect($page->text('#page'))->toBe('/page-a')
        ->and($loads['/page-a'])->toBe(2);

    $page->forward();

    expect($page->text('#page'))->toBe('/page-b')
        ->and($loads['/page-b'])->toBe(3);
});
