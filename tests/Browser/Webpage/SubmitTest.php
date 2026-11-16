<?php

declare(strict_types=1);

it('may submit a form', function (): void {
    Route::get('/', fn (): string => '
        <form id="form" action="#">
            <button type="submit">Send</button>
        </form>
        <span id="result">Not submitted</span>

        <script>
            document.getElementById("form").addEventListener("submit", function (event) {
                event.preventDefault();
                document.getElementById("result").textContent = "Submitted";
            });
        </script>
    ');

    $page = visit('/');

    $page->submit();

    expect($page->text('#result'))->toBe('Submitted');
});

it('submits only once when the page takes longer than an attempt to handle the submit', function (): void {
    Route::get('/', fn (): string => '
        <form id="form" action="#">
            <button type="submit">Send</button>
        </form>
        <span id="submits">0</span>

        <script>
            let submits = 0;

            document.getElementById("form").addEventListener("submit", function (event) {
                event.preventDefault();

                submits += 1;
                document.getElementById("submits").textContent = String(submits);

                const until = performance.now() + 1500;

                while (performance.now() < until) {}
            });
        </script>
    ');

    $page = visit('/');

    $page->submit();

    expect($page->text('#submits'))->toBe('1');
});
