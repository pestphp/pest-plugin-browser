<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('waits for a navigation to the given path', function (): void {
    Route::get('/', fn (): string => '<script>setTimeout(() => { location.assign("/next"); }, 500);</script>Start');
    Route::get('/next', fn (): string => 'Next');

    $page = visit('/');

    $page->page()->waitForURL('/next');

    expect($page->page()->url())->toEndWith('/next');
});

it('matches the full url with a wildcard', function (): void {
    Route::get('/', fn (): string => '<script>setTimeout(() => { location.assign("/next"); }, 500);</script>Start');
    Route::get('/next', fn (): string => 'Next');

    $page = visit('/');

    $page->page()->waitForURL('http://*/next');

    expect($page->page()->url())->toEndWith('/next');
});

it('fails when the url never matches', function (): void {
    Route::get('/', fn (): string => 'Start');

    $page = visit('/');

    $page->page()->waitForURL('/never');
})->throws(ExpectationFailedException::class);
