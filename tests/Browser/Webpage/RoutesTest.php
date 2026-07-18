<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Playwright\Route as PlaywrightRoute;

it('may continue a request', function (): void {
    Route::get('/', fn (): string => '
        <script>
            fetch("/message")
                .then(response => response.text())
                .then(message => document.body.textContent = message)
                .catch(() => document.body.textContent = "aborted");
        </script>
    ');

    Route::get('/message', fn (): string => 'continued');

    $page = visit('/')->withRoute('**/message', fn (PlaywrightRoute $route) => $route->continue());

    $page->assertSee('continued');
    $page->assertDontSee('aborted');
    $page->assertDontSee('fulfilled');
});

it('may abort a request', function (): void {
    Route::get('/', fn (): string => '
        <script>
            fetch("/message")
                .then(response => response.text())
                .then(message => document.body.textContent = message)
                .catch(() => document.body.textContent = "aborted");
        </script>
    ');

    Route::get('/message', fn (): string => 'continued');

    $page = visit('/')->withRoute('**/message', fn (PlaywrightRoute $route) => $route->abort());

    $page->assertDontSee('continued');
    $page->assertSee('aborted');
    $page->assertDontSee('fulfilled');
});

it('may fulfill a request', function (): void {
    Route::get('/', fn (): string => '
        <script>
            fetch("/message")
                .then(response => response.text())
                .then(message => document.body.textContent = message)
                .catch(() => document.body.textContent = "aborted");
        </script>
    ');

    Route::get('/message', fn (): string => 'continued');

    $page = visit('/')->withRoute('**/message', fn (PlaywrightRoute $route) => $route->fulfill([
        'status' => 200,
        'body' => 'fulfilled',
    ]));

    $page->assertDontSee('continued');
    $page->assertDontSee('aborted');
    $page->assertSee('fulfilled');
});
