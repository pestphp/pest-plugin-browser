<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

test('may add and retrieve cookies via the browser context', function (): void {
    Route::get('/', fn (): string => '<html><body>Cookies</body></html>');

    $page = visit('/')->assertSee('Cookies');

    $page->page()->context()->addCookies([
        [
            'name' => 'context_cookie',
            'value' => 'context_value',
            'domain' => '127.0.0.1',
            'path' => '/',
        ],
    ]);

    $cookies = $page->page()->context()->cookies();

    $cookie = collect($cookies)->firstWhere('name', 'context_cookie');

    expect($cookie)->not->toBeNull();
    expect($cookie['value'])->toBe('context_value');
});

test('may retrieve the full storage state including added cookies', function (): void {
    Route::get('/', fn (): string => '<html><body>Storage</body></html>');

    $page = visit('/')->assertSee('Storage');

    $page->page()->context()->addCookies([
        [
            'name' => 'state_cookie',
            'value' => 'state_value',
            'domain' => '127.0.0.1',
            'path' => '/',
        ],
    ]);

    $state = $page->page()->context()->storageState();

    expect($state)->toHaveKeys(['cookies', 'origins']);
    expect(collect($state['cookies'])->firstWhere('name', 'state_cookie'))->not->toBeNull();
});

test('may clear all cookies from the browser context', function (): void {
    Route::get('/', fn (): string => '<html><body>Clear cookies</body></html>');

    $page = visit('/')->assertSee('Clear cookies');

    $page->page()->context()->addCookies([
        [
            'name' => 'to_be_cleared',
            'value' => 'value',
            'domain' => '127.0.0.1',
            'path' => '/',
        ],
    ]);

    expect($page->page()->context()->cookies())->not->toBeEmpty();

    $page->page()->context()->clearCookies();

    expect($page->page()->context()->cookies())->toBeEmpty();
});
