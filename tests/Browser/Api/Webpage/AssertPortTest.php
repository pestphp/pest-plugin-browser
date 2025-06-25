<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert URL port matches expected port', function (): void {
    Route::get('/test-port', fn (): string => 'Test Port Page');

    $page = visit('/test-port');

    // Extract the port from the current URL
    $port = parse_url(url('/test-port'), PHP_URL_PORT) ?? '80';

    $page->assertPortIs((string) $port);
});

it('may fail when asserting URL port matches but it does not', function (): void {
    Route::get('/test-port', fn (): string => 'Test Port Page');

    $page = visit('/test-port');

    // Use a port that's definitely not the current one
    $wrongPort = '9999';

    $page->assertPortIs($wrongPort);
})->throws(ExpectationFailedException::class);

it('may assert URL port does not match expected port', function (): void {
    Route::get('/test-port', fn (): string => 'Test Port Page');

    $page = visit('/test-port');

    // Use a port that's definitely not the current one
    $wrongPort = '9999';

    $page->assertPortIsNot($wrongPort);
});

it('may fail when asserting URL port does not match but it does', function (): void {
    Route::get('/test-port', fn (): string => 'Test Port Page');

    $page = visit('/test-port');

    // Extract the port from the current URL
    $port = parse_url(url('/test-port'), PHP_URL_PORT) ?? '80';

    $page->assertPortIsNot((string) $port);
})->throws(ExpectationFailedException::class);
