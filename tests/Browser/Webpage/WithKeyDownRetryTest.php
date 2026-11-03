<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('does not replay a withKeyDown callback whose first attempt is slow', function (): void {
    Route::get('/', fn (): string => '
        <input id="field" autofocus>
        <script>
            window.presses = 0;

            document.addEventListener("keydown", function (event) {
                if (event.key !== "a") {
                    return;
                }

                if (++window.presses === 1) {
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

    $page->withKeyDown('Shift', fn ($page) => $page->keys('#field', 'a'));

    $page->assertScript('window.presses', 1);
})->repeat(3);
