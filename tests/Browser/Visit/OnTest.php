<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('keeps the same page across separate calls after choosing a device', function (string $device): void {
    Route::get('/', fn (): string => '
        <button id="toggle" type="button" onclick="document.getElementById(\'result\').textContent = \'Clicked\'">Toggle</button>
        <span id="result">Waiting</span>
    ');

    $page = visit('/')->on()->{$device}();

    $page->click('#toggle');

    $page->assertSeeIn('#result', 'Clicked');
})->with(['desktop', 'mobile', 'iPhone14Pro']);

it('keeps the same page across separate calls without choosing a device', function (): void {
    Route::get('/', fn (): string => '
        <button id="toggle" type="button" onclick="document.getElementById(\'result\').textContent = \'Clicked\'">Toggle</button>
        <span id="result">Waiting</span>
    ');

    $page = visit('/')->on();

    $page->click('#toggle');

    $page->assertSeeIn('#result', 'Clicked');
});
