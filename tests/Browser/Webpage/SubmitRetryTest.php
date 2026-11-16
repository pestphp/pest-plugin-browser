<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('does not replay a submit whose first attempt is slow', function (): void {
    Route::get('/', fn (): string => '
        <form id="form">
            <button type="submit">Save</button>
        </form>
        <script>
            window.submits = 0;

            document.getElementById("form").addEventListener("submit", function (event) {
                event.preventDefault();

                if (++window.submits === 1) {
                    const until = Date.now() + 1200;

                    while (Date.now() < until) {
                        // Busy main thread, as on a loaded CI runner, so the first
                        // attempt overruns the 1000 ms per-attempt budget.
                    }
                }
            });
        </script>
    ');

    $page = visit('/');

    $page->submit();

    $page->assertScript('window.submits', 1);
})->repeat(3);

it('may submit a form that a click reveals', function (): void {
    Route::get('/', fn (): string => '
        <button id="reveal" type="button">Reveal</button>
        <form id="form" hidden>
            <button type="submit">Save</button>
        </form>
        <script>
            window.submits = 0;

            document.getElementById("reveal").addEventListener("click", function () {
                setTimeout(function () {
                    document.getElementById("form").hidden = false;
                }, 50);
            });

            document.getElementById("form").addEventListener("submit", function (event) {
                event.preventDefault();
                window.submits++;
            });
        </script>
    ');

    $page = visit('/');

    $page->click('Reveal');
    $page->submit();

    $page->assertScript('window.submits', 1);
});
