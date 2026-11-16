<?php

declare(strict_types=1);

it('may send keys to an element', function (): void {
    Route::get('/', fn (): string => '
        <input id="input" type="text">
        <script>
            document.getElementById("input").addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    this.value = "Enter pressed";
                }
            });
        </script>
    ');

    $page = visit('/');

    $page->keys('#input', 'Enter');

    expect($page->value('#input'))->toBe('Enter pressed');
});

it('may send multiple keys to an element', function (): void {
    Route::get('/', fn (): string => '
        <input id="input" type="text">
    ');

    $page = visit('/');

    $page->keys('#input', ['a', 'b', 'c']);

    expect($page->value('input'))->toBe('abc');
});

it('writes uppercase while holding shift', function (): void {
    Route::get('/', fn (): string => '
        <input id="input" type="text">
    ');

    $page = visit('/');

    $page->keys('#input', ['KeyA', 'KeyB', 'KeyC']);
    $page->withKeyDown('Shift', function () use ($page): void {
        $page->keys('#input', ['KeyD', 'KeyE', 'KeyF']);
    });
    $page->keys('#input', ['KeyG', 'KeyH', 'KeyI']);

    expect($page->value('input'))->toBe('abcDEFghi');
});

it('runs the withKeyDown callback only once when an action inside it is slow', function (): void {
    Route::get('/', fn (): string => '
        <button id="slow" type="button">Slow</button>
        <span id="clicks">0</span>

        <script>
            let clicks = 0;

            document.getElementById("slow").addEventListener("click", function () {
                clicks += 1;
                document.getElementById("clicks").textContent = String(clicks);

                if (clicks === 1) {
                    const until = performance.now() + 1500;

                    while (performance.now() < until) {}
                }
            });
        </script>
    ');

    $page = visit('/');

    $page->withKeyDown('Shift', function () use ($page): void {
        $page->click('#slow');
    });

    expect($page->text('#clicks'))->toBe('1');
});
