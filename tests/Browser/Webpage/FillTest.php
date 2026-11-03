<?php

declare(strict_types=1);

it('may fill text in an input field', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text">');

    $page = visit('/');

    $page->fill('#name', 'John Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('may fill text in a textarea', function (): void {
    Route::get('/', fn (): string => '<textarea id="message" name="message"></textarea>');

    $page = visit('/');

    $page->fill('#message', 'Hello World');

    expect($page->value('#message'))->toBe('Hello World');
});

it('may clear the field before typing', function (): void {
    Route::get('/', fn (): string => '<input id="name" name="name" type="text" value="Initial Value">');

    $page = visit('/');

    $page->fill('#name', 'John Doe');

    expect($page->value('#name'))->toBe('John Doe');
});

it('can fill an input with exact match css selectors', function (string $selector): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="text" name="username" id="username" value="Initial Value">
        </form>
    ');

    $page = visit('/');

    $page->fill($selector, 'New Username');
    expect($page->value('#username'))->toBe('New Username');
})->with([
    '[name]',
    '[name*="username"]',
    '[name^="username"]',
    '[name$="username"]',
    'input[name="username"]',
]);

it('fills only once when the page takes longer than an attempt to handle the input', function (string $method): void {
    Route::get('/', fn (): string => '
        <input id="name" type="text">
        <span id="inputs">0</span>

        <script>
            let inputs = 0;

            document.getElementById("name").addEventListener("input", function () {
                inputs += 1;
                document.getElementById("inputs").textContent = String(inputs);

                if (inputs === 1) {
                    const until = performance.now() + 1500;

                    while (performance.now() < until) {}
                }
            });
        </script>
    ');

    $page = visit('/');

    $page->{$method}('#name', 'John Doe');

    expect($page->text('#inputs'))->toBe('1');
})->with(['fill', 'type']);

it('may fill a field that the previous action reveals', function (): void {
    Route::get('/', fn (): string => '
        <button type="button">Reveal</button>
        <div id="form"></div>

        <script>
            document.querySelector("button").addEventListener("click", function () {
                setTimeout(function () {
                    document.getElementById("form").innerHTML = \'<input name="email" type="text">\';
                }, 500);
            });
        </script>
    ');

    $page = visit('/');

    $page->click('Reveal');
    $page->fill('email', 'john@example.com');

    expect($page->value('[name="email"]'))->toBe('john@example.com');
});
