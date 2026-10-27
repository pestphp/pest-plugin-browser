<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\On;

it('keeps page state between separate method calls', function (string $device): void {
    Route::get('/', fn (): string => '
        <html>
        <head></head>
        <body>
            <button id="increment">Increment</button>
            <span id="count">0</span>
            <script>
                let count = 0;
                document.getElementById("increment").addEventListener("click", () => {
                    count++;
                    document.getElementById("count").textContent = String(count);
                });
            </script>
        </body>
        </html>
    ');

    /** @var On $page */
    $page = visit('/')->on()->{$device}();

    $page->click('#increment');
    $page->click('#increment');

    $page->assertSeeIn('#count', '2');
})->with(['desktop', 'mobile', 'iPhone14Pro']);

it('keeps page state between separate calls without choosing a device', function (): void {
    Route::get('/', fn (): string => '
        <html>
        <head></head>
        <body>
            <button id="increment">Increment</button>
            <span id="count">0</span>
            <script>
                let count = 0;
                document.getElementById("increment").addEventListener("click", () => {
                    count++;
                    document.getElementById("count").textContent = String(count);
                });
            </script>
        </body>
        </html>
    ');

    /** @var On $page */
    $page = visit('/')->on();

    $page->click('#increment');
    $page->click('#increment');

    $page->assertSeeIn('#count', '2');
});
