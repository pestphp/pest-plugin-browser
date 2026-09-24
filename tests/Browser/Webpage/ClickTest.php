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

it('clicks only once when the page takes longer than an attempt to handle the click', function (): void {
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

    $page->click('#slow');

    expect($page->text('#clicks'))->toBe('1');
});
