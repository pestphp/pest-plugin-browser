<?php

declare(strict_types=1);

use Amp\ByteStream\ReadableBuffer;
use Amp\ByteStream\ReadableResourceStream;
use Illuminate\Http\Request;
use Pest\Browser\Drivers\LaravelHttpServer;

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

it('serves JS assets as string body instead of stream', function (): void {
    // Regression test: the old implementation used fread() + ReadableResourceStream
    // which could truncate JS files at 8192 bytes inside Amp's event loop.
    // The fix uses file_get_contents() and returns the content as a string body
    // (ReadableBuffer) instead of a stream (ReadableResourceStream).
    $path = public_path('string-body-test.js');
    @file_put_contents($path, "console.log('ok');");

    $server = new LaravelHttpServer('127.0.0.1', 0);

    $method = new ReflectionMethod($server, 'asset');

    $response = $method->invoke($server, $path);

    $body = $response->getBody();

    expect($body)->toBeInstanceOf(ReadableBuffer::class)
        ->and($body)->not->toBeInstanceOf(ReadableResourceStream::class);
});

it('sets content-length header on JS asset responses', function (): void {
    // Regression test: the old implementation used ReadableResourceStream which
    // caused Response::setBody() to remove the content-length header.
    $jsContent = "console.log('content-length test');";
    $path = public_path('cl-test.js');
    @file_put_contents($path, $jsContent);

    $server = new LaravelHttpServer('127.0.0.1', 0);

    $method = new ReflectionMethod($server, 'asset');

    $response = $method->invoke($server, $path);

    expect($response->getHeader('content-length'))->toBe((string) strlen($jsContent))
        ->and($response->getHeader('content-type'))->toBe('text/javascript');
});

it('serves large JS files completely', function (): void {
    // Regression test: fread() can return fewer bytes than requested inside
    // Amp's event loop (limited to 8192 per read). Verify files >8KB are served intact.
    $padding = str_repeat("// padding line to exceed 8192 bytes\n", 300);
    $jsContent = $padding."window.__pest_large_file_marker = 'COMPLETE';";

    @file_put_contents(public_path('large.js'), $jsContent);

    Route::get('/large-js-test', fn (): string => '<html><body><script src="/large.js"></script></body></html>');

    visit('/large-js-test')
        ->assertScript('window.__pest_large_file_marker', 'COMPLETE');
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
