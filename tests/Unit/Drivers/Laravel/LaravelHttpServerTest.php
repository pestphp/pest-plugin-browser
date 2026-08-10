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

it('serves JS files bigger than the stream chunk size', function (): void {
    @file_put_contents(
        $filepath = public_path('large.js'),
        str_repeat('// '.str_repeat('x', 77)."\n", 110)."document.title = 'end of file';",
    );

    expect(filesize($filepath))->toBeGreaterThan(8192);

    Route::get('/large', fn (): string => '<html><head><script src="/large.js"></script></head><body>Hello</body></html>');

    visit('/large')
        ->assertTitle('end of file')
        ->assertNoJavascriptErrors();
});

it('serves empty JS files', function (): void {
    @file_put_contents(public_path('empty.js'), '');

    visit('/empty.js')->assertDontSee('Internal Server Error');
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
