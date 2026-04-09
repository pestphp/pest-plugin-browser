<?php

declare(strict_types=1);

use Illuminate\Http\Request;

use function Pest\Laravel\withServerVariables;
use function Pest\Laravel\withUnencryptedCookie;

it('rewrites the URLs on JS files', function (): void {
    @file_put_contents(
        public_path('app.js'),
        <<<'JS'
        console.log('Hello http://localhost');
        JS,
    );

    $page = visit('/app.js');

    $page->assertSee('http://127.0.0.1')
        ->assertDontSee('http://localhost');
});

it('serves JS files larger than 8192 bytes in full without truncation', function (): void {
    // Create a JS file well over 8KB with a sentinel value at the very end.
    // Before the fix, ReadableResourceStream on a php://temp stream would only
    // deliver the first 8192-byte chunk, so the sentinel would be missing.
    $padding = str_repeat('//' . str_repeat('x', 78) . PHP_EOL, 110); // ~8250 bytes of padding
    @file_put_contents(
        public_path('large.js'),
        $padding . "console.log('END_SENTINEL');",
    );

    visit('/large.js')->assertSee('END_SENTINEL');
});

it('includes cookies set in the test', function (): void {
    Route::get('/cookies', fn (Request $request): array => $request->cookies->all());

    withUnencryptedCookie('test-cookie', value: 'test value');
    visit('/cookies')
        ->assertSee(json_encode(['test-cookie' => 'test value']));
});

it('includes server variables set in the test', function (): void {
    Route::get('/server-variables', fn (Request $request): array => $request->server->all());

    withServerVariables(['test-server-key' => 'test value']);
    visit('/server-variables')
        ->assertSee('"test-server-key":"test value"');
});
