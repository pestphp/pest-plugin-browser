<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('may click a link', function (): void {
    Route::get('/', fn (): string => '<a href="/about">About Us</a>');
    Route::get('/about', fn (): string => 'About Page');

    $page = visit('/');
    $page->assertUrlIs(url('/'));

    $page->click('About Us');
    $page->assertUrlIs(url('/about'));
    $page->assertSee('About Page');
});

it('may click a javascript link that takes a few miliseconds to redirect', function (): void {
    Route::get('/', fn (): string => '
        <a href="/about" id="about-link">Really</a>

        <button id="click-about">Click About</button>

        <script>
            document.getElementById("click-about").addEventListener("click", function() {
                setTimeout(function() {
                    document.getElementById("about-link").click();
                }, 1);
            });
        </script>
    ');

    Route::get('/about', fn (): string => 'About Page');

    $page = visit('/');
    $page->assertUrlIs(url('/'));

    $page->click('Click About');

    $page->assertUrlIs(url('/about'));
    $page->assertSee('About Page');
});

it('may click a link with an id selector', function (): void {
    Route::get('/', fn (): string => '<a id="about-link" href="/about">About Us</a>');
    Route::get('/about', fn (): string => 'About Page');

    $page = visit('/');

    $page->click('#about-link');

    $page->assertSee('About Page');
});

it('can click elements via exact match css selectors', function (string $selector): void {
    Route::get('/', fn (): string => '
        <form>
            <button type="button" value="Click Me" name="test" onclick="document.getElementById(\'result\').textContent = \'Button Clicked\'">
            <div id="result"></div>
        </form>
    ');

    $page = visit('/');

    $page->click($selector);

    expect($page->text('#result'))->toBe('Button Clicked');
})->with([
    '[name]',
    '[name*="test"]',
    '[name^="test"]',
    '[name$="test"]',
    'button[name="test"]',
]);

it('does not re-fire a click whose first attempt is slow', function (): void {
    Route::get('/', fn (): string => '
        <button id="open">Open</button>
        <div id="overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5);">
            <p>Dialog</p>
        </div>

        <script>
            window.downs = 0;

            document.getElementById("open").addEventListener("pointerdown", function () {
                if (++window.downs === 1) {
                    const until = Date.now() + 1200;

                    while (Date.now() < until) {
                        // Simulate a busy main thread, as on a loaded CI runner.
                    }
                }
            });

            document.getElementById("open").addEventListener("click", function () {
                document.getElementById("overlay").style.display = "block";
            });
        </script>
    ');

    $page = visit('/');

    $page->click('#open');

    $page->assertScript('window.downs', 1);
    $page->assertVisible('#overlay p');
});
