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

test('click may accept options', function (): void {
    Route::get('/', fn (): string => '
    <p id="result"></p>
    <button
        id="button"
        ondblclick="document.getElementById(\'result\').textContent = \'Option-2 clicked\'"
    >
        Click Me
    </button>');

    $page = visit('/');

    $page->click('#button');
    $page->assertDontSeeIn('#result', 'Option-2 clicked');

    // clickCount => 2 is considered as double click
    $page->click('#button', options: ['clickCount' => 2]);
    $page->assertSeeIn('#result', 'Option-2 clicked');
});

it('can double click an element', function (): void {
    Route::get('/', fn (): string => '
    <p id="result"></p>
    <button
        id="button"
        ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'"
    >
        Double Click Me
    </button>');

    $page = visit('/');

    $page->doubleClick('#button');
    $page->assertSeeIn('#result', 'Double Clicked');
});

it('can double click an element with text selector', function (): void {
    Route::get('/', fn (): string => '
    <p id="result"></p>
    <div
        ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'"
    >
        Double Click Me
    </div>');

    $page = visit('/');

    $page->doubleClick('Double Click Me');
    $page->assertSeeIn('#result', 'Double Clicked');
});

it('can double click on different element types', function (string $element): void {
    Route::get('/', fn (): string => "
        <p id=\"result\"></p>
        $element
    ");

    $page = visit('/');

    $page->doubleClick('#clickable');
    $page->assertSeeIn('#result', 'Double Clicked');
})->with([
    '<button id="clickable" ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'"></button>',
    '<div id="clickable" ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'">Button</div>',
    '<input type="button" id="clickable" ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'"></input>',
    '<input type="submit" id="clickable" ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'"></input>',
    '<input type="reset" id="clickable" ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'"></input>',
    '<input type="checkbox" id="clickable" ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'"></input>',
    '<input type="radio" id="clickable" ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'"></input>',
    '<a id="clickable" href="#" ondblclick="document.getElementById(\'result\').textContent = \'Double Clicked\'">Button</a>',
]);

it('can double click to select text content', function (): void {
    Route::get('/', fn (): string => '
        <p id="selectable" onmouseup="document.getElementById(\'result\').textContent = window.getSelection().toString();">
            This is some selectable text content that can be selected
        </p>
        <p id="result"></p>
    ');

    $page = visit('/');

    $page->doubleClick('#selectable');
    $page->assertSeeIn('#result', 'selected');
});
