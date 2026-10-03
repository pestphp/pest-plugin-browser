<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('runs the first script against the requested document after visiting and navigating', function (): void {
    Route::get('/navigation-first', fn (): string => '<!doctype html><html lang="en"><head><title>First document</title></head><body>First</body></html>');
    Route::get('/navigation-next', fn (): string => '<!doctype html><html lang="en"><head><title>Next document</title></head><body>Next</body></html>');

    $page = visit('/navigation-first');

    expect($page->script('document.title'))->toBe('First document');

    $page->navigate('/navigation-next');

    expect($page->script('document.title'))->toBe('Next document');
});
