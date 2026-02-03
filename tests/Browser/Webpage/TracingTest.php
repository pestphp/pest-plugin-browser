<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Playwright\Tracing;

it('captures a tracing', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->startTracing();
    $page->stopTracing('trace.zip');

    expect(file_exists(Tracing::path('trace.zip')))
        ->toBeTrue();
});

it('captures a tracing with default filename', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->startTracing();
    $page->stopTracing();

    expect(file_exists(Tracing::path()))
        ->toBeTrue();
});
