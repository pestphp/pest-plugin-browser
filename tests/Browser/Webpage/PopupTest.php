<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\PendingAwaitablePopup;
use PHPUnit\Framework\ExpectationFailedException;

it('can handle window.open', function (): void {
    Route::get('/', fn (): string => '
        <button id="popup-btn" onclick="
            window.open(\'/popup\', \'Popup-target\', \'width=600,height=400\');
            document.getElementById(\'result\').textContent = \'Window opened\';
        ">Open Window</button>
        <div id="result"></div>
    ');

    Route::get('/popup', fn (): string => '
        <div id="popup-content">Another page</div>
    ');

    $page = visit('/');

    $popup = $page->pendingPopup();
    $page->click('#popup-btn');

    $popup->assertSeeIn('#popup-content', 'Another page');

    expect($page->text('#result'))->toBe('Window opened');
});

it('can handle link with target', function (): void {
    Route::get('/', fn (): string => '
        <a id="popup-link" href="/popup" target="_blank">Open Link in new tab</a>
    ');

    Route::get('/popup', fn (): string => '
        <div id="popup-content">Another tab</div>
    ');

    $page = visit('/');

    $popup = $page->pendingPopup();
    $page->click('#popup-link');

    $popup->assertSeeIn('#popup-content', 'Another tab');
});

it('can interact with popup', function (): void {
    Route::get('/', fn (): string => '
        <button id="popup-btn" onclick="window.open(\'/popup\'); document.getElementById(\'result\').textContent = \'Window opened\';">Open Window</button>
        <div id="result"></div>
    ');

    Route::get('/popup', fn (): string => '
        <button id="change-btn" onclick="document.getElementById(\'result\').textContent = \'altered\';">Change text</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $popup = $page->pendingPopup();
    $page->click('#popup-btn');

    $popup->click('#change-btn');

    expect($page->text('#result'))->toBe('Window opened');

    expect($popup->text('#result'))->toBe('altered');
});

it('removes pending popup from page when opened', function (): void {
    Route::get('/', fn (): string => '
        <button id="popup-btn" onclick="window.open(\'/popup\');">Open Window</button>
    ');

    Route::get('/popup', fn (): string => '
        <div id="popup-content">Another page</div>
    ');

    $page = visit('/');

    $popup = $page->pendingPopup();
    $page->click('#popup-btn');

    $popup->assertSeeIn('#popup-content', 'Another page');

    expect($page->hasPendingPopup())->toBeFalse();
});

it('can remove pending popup', function (): void {
    $page = visit('/');

    expect($page->hasPendingPopup())->toBeFalse();

    $popup = $page->pendingPopup();
    expect($popup)->toBeInstanceOf(PendingAwaitablePopup::class);
    expect($page->hasPendingPopup())->toBeTrue();

    $page->removePendingPopup();
    expect($page->hasPendingPopup())->toBeFalse();
});

it('can open popups for multiple pages', function (): void {
    Route::get('/a', fn (): string => '
        <button id="popup-btn" onclick="window.open(\'/popup-a\');">Open Window</button>
    ');
    Route::get('/b', fn (): string => '
        <button id="popup-btn" onclick="window.open(\'/popup-b\');">Open Window</button>
    ');

    Route::get('/popup-a', fn (): string => '
        <div id="popup-content">Popup Window A</div>
    ');
    Route::get('/popup-b', fn (): string => '
        <div id="popup-content">Popup Window B</div>
    ');

    $pageA = visit('/a');
    $pageB = visit('/b');

    $popupA = $pageA->pendingPopup();
    $popupB = $pageB->pendingPopup();

    $pageB->click('#popup-btn');
    $pageA->click('#popup-btn');

    $popupA->assertSeeIn('#popup-content', 'Popup Window A');
    $popupB->assertSeeIn('#popup-content', 'Popup Window B');
});

it('can open a nested popup', function (): void {
    Route::get('/', fn (): string => '
        <button id="popup-btn" onclick="window.open(\'/popup\');">Open Window</button>
    ');

    Route::get('/popup', fn (): string => '
        <button id="nested-popup-btn" onclick="window.open(\'/nested-popup\');">Open Nested Window</button>
    ');
    Route::get('/nested-popup', fn (): string => '
        <div id="popup-content">Nested Window</div>
    ');

    $page = visit('/');

    $popup = $page->pendingPopup();
    $page->click('#popup-btn');

    $nested = $popup->pendingPopup();
    $popup->click('#nested-popup-btn');

    $nested->assertSeeIn('#popup-content', 'Nested Window');
});

it('fails interaction if popup does not open', function (): void {
    $page = visit('/');

    $popup = $page->pendingPopup();

    $popup->click('#no-btn');
})->throws(ExpectationFailedException::class, 'No popup opened');

it('can check for smoke in popup window', function (): void {
    Route::get('/', fn (): string => '
        <button id="popup-btn" onclick="window.open(\'/popup\')">Open Window</button>
    ');

    Route::get('/popup', fn (): string => '
        <button id="log-btn" onclick="console.log(\'popped up and logged\');">Log Message</button>
        <div>Some Content</div>
    ');

    $page = visit('/');

    $popup = $page->pendingPopup();
    $page->click('#popup-btn');

    $popup->click('#log-btn');

    $popup->assertNoConsoleLogs();
})->throws(ExpectationFailedException::class, 'but found 1: popped up and logged');
