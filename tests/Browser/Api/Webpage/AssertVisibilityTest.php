<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert element is visible', function (): void {
    Route::get('/', fn (): string => '<div id="visible">Visible Element</div><div id="hidden" style="display: none;">Hidden Element</div>');

    $page = visit('/');

    $page->assertVisible('#visible');
});

it('may fail when asserting element is visible but it is not', function (): void {
    Route::get('/', fn (): string => '<div id="visible">Visible Element</div><div id="hidden" style="display: none;">Hidden Element</div>');

    $page = visit('/');

    $page->assertVisible('#hidden');
})->throws(ExpectationFailedException::class);

it('may assert element is present in the DOM', function (): void {
    Route::get('/', fn (): string => '<div id="visible">Visible Element</div><div id="hidden" style="display: none;">Hidden Element</div>');

    $page = visit('/');

    $page->assertPresent('#visible');
    $page->assertPresent('#hidden');
});

it('may fail when asserting element is present but it is not', function (): void {
    Route::get('/', fn (): string => '<div id="visible">Visible Element</div>');

    $page = visit('/');

    $page->assertPresent('#non-existent');
})->throws(ExpectationFailedException::class);

it('may assert element is not present in the DOM', function (): void {
    Route::get('/', fn (): string => '<div id="visible">Visible Element</div>');

    $page = visit('/');

    $page->assertNotPresent('#non-existent');
});

it('may fail when asserting element is not present but it is', function (): void {
    Route::get('/', fn (): string => '<div id="visible">Visible Element</div>');

    $page = visit('/');

    $page->assertNotPresent('#visible');
})->throws(ExpectationFailedException::class);

it('may assert element is not visible', function (): void {
    Route::get('/', fn (): string => '<div id="visible">Visible Element</div><div id="hidden" style="display: none;">Hidden Element</div>');

    $page = visit('/');

    $page->assertMissing('#hidden');
    $page->assertMissing('#non-existent');
});

it('may fail when asserting element is not visible but it is', function (): void {
    Route::get('/', fn (): string => '<div id="visible">Visible Element</div><div id="hidden" style="display: none;">Hidden Element</div>');

    $page = visit('/');

    $page->assertMissing('#visible');
})->throws(ExpectationFailedException::class);
