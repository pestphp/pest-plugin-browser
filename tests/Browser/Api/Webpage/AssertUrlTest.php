<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert current URL matches expected URL', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    $page->assertUrlIs(url('/test-url'));
});

it('may fail when asserting URL matches but it does not', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    $page->assertUrlIs(url('/wrong-url'));
})->throws(ExpectationFailedException::class);

it('may assert URL scheme matches expected scheme', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    $page->assertSchemeIs('http');
});

it('may fail when asserting URL scheme matches but it does not', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    $page->assertSchemeIs('https');
})->throws(ExpectationFailedException::class);

it('may assert URL scheme does not match expected scheme', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    $page->assertSchemeIsNot('https');
});

it('may fail when asserting URL scheme does not match but it does', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    $page->assertSchemeIsNot('http');
})->throws(ExpectationFailedException::class);

it('may assert URL host matches expected host', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    // Extract the host from the current URL
    $host = parse_url(url('/test-url'), PHP_URL_HOST);

    $page->assertHostIs($host);
});

it('may fail when asserting URL host matches but it does not', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    $page->assertHostIs('wrong-host.com');
})->throws(ExpectationFailedException::class);

it('may assert URL host does not match expected host', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    $page->assertHostIsNot('wrong-host.com');
});

it('may fail when asserting URL host does not match but it does', function (): void {
    Route::get('/test-url', fn (): string => 'Test URL Page');

    $page = visit('/test-url');

    // Extract the host from the current URL
    $host = parse_url(url('/test-url'), PHP_URL_HOST);

    $page->assertHostIsNot($host);
})->throws(ExpectationFailedException::class);

it('may assert URL path matches expected path', function (): void {
    Route::get('/test-path', fn (): string => 'Test Path Page');

    $page = visit('/test-path');

    $page->assertPathIs('/test-path');
});

it('may fail when asserting URL path matches but it does not', function (): void {
    Route::get('/test-path', fn (): string => 'Test Path Page');

    $page = visit('/test-path');

    $page->assertPathIs('/wrong-path');
})->throws(ExpectationFailedException::class);

it('may assert URL path does not match expected path', function (): void {
    Route::get('/test-path', fn (): string => 'Test Path Page');

    $page = visit('/test-path');

    $page->assertPathIsNot('/wrong-path');
});

it('may fail when asserting URL path does not match but it does', function (): void {
    Route::get('/test-path', fn (): string => 'Test Path Page');

    $page = visit('/test-path');

    $page->assertPathIsNot('/test-path');
})->throws(ExpectationFailedException::class);

it('may assert URL path begins with expected path', function (): void {
    Route::get('/test/nested/path', fn (): string => 'Test Nested Path Page');

    $page = visit('/test/nested/path');

    $page->assertPathBeginsWith('/test');
});

it('may fail when asserting URL path begins with expected path but it does not', function (): void {
    Route::get('/test/nested/path', fn (): string => 'Test Nested Path Page');

    $page = visit('/test/nested/path');

    $page->assertPathBeginsWith('/wrong');
})->throws(ExpectationFailedException::class);

it('may assert URL path ends with expected path', function (): void {
    Route::get('/test/nested/path', fn (): string => 'Test Nested Path Page');

    $page = visit('/test/nested/path');

    $page->assertPathEndsWith('/path');
});

it('may fail when asserting URL path ends with expected path but it does not', function (): void {
    Route::get('/test/nested/path', fn (): string => 'Test Nested Path Page');

    $page = visit('/test/nested/path');

    $page->assertPathEndsWith('/wrong');
})->throws(ExpectationFailedException::class);

it('may assert URL path contains expected string', function (): void {
    Route::get('/test/nested/path', fn (): string => 'Test Nested Path Page');

    $page = visit('/test/nested/path');

    $page->assertPathContains('nested');
});

it('may fail when asserting URL path contains expected string but it does not', function (): void {
    Route::get('/test/nested/path', fn (): string => 'Test Nested Path Page');

    $page = visit('/test/nested/path');

    $page->assertPathContains('wrong');
})->throws(ExpectationFailedException::class);

it('may assert URL has expected query string parameter', function (): void {
    Route::get('/test-query', fn (): string => 'Test Query Page');

    $page = visit('/test-query?param=value');

    $page->assertQueryStringHas('param');
    $page->assertQueryStringHas('param', 'value');
});

it('may fail when asserting URL has query string parameter but it does not', function (): void {
    Route::get('/test-query', fn (): string => 'Test Query Page');

    $page = visit('/test-query?param=value');

    $page->assertQueryStringHas('missing');
})->throws(ExpectationFailedException::class);

it('may fail when asserting URL has query string parameter with specific value but it does not', function (): void {
    Route::get('/test-query', fn (): string => 'Test Query Page');

    $page = visit('/test-query?param=value');

    $page->assertQueryStringHas('param', 'wrong');
})->throws(ExpectationFailedException::class);

it('may assert URL does not have expected query string parameter', function (): void {
    Route::get('/test-query', fn (): string => 'Test Query Page');

    $page = visit('/test-query?param=value');

    $page->assertQueryStringMissing('missing');
});

it('may fail when asserting URL does not have query string parameter but it does', function (): void {
    Route::get('/test-query', fn (): string => 'Test Query Page');

    $page = visit('/test-query?param=value');

    $page->assertQueryStringMissing('param');
})->throws(ExpectationFailedException::class);
