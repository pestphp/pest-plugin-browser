<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

test('may set a single cookie via withCookie()', function (): void {
    Route::get('/cookie-check', fn (Request $request): array => $request->cookies->all());

    visit('/cookie-check')
        ->withCookie('via_browser', 'forwarded')
        ->assertSee('"via_browser":"forwarded"');
});

test('may set multiple cookies via withCookies()', function (): void {
    Route::get('/cookie-check', fn (Request $request): array => $request->cookies->all());

    visit('/cookie-check')
        ->withCookies([
            ['name' => 'first', 'value' => 'one'],
            ['name' => 'second', 'value' => 'two'],
        ])
        ->assertSee('"first":"one"')
        ->assertSee('"second":"two"');
});

test('subsequent withCookie() calls accumulate', function (): void {
    Route::get('/cookie-check', fn (Request $request): array => $request->cookies->all());

    visit('/cookie-check')
        ->withCookie('a', '1')
        ->withCookie('b', '2')
        ->assertSee('"a":"1"')
        ->assertSee('"b":"2"');
});
