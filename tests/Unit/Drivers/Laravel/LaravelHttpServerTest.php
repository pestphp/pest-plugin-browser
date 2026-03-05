<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

it('restores original superglobals after request handling', function (): void {
    $originalGet = $_GET;
    $originalPost = $_POST;
    $originalRequest = $_REQUEST;
    $originalServer = $_SERVER;
    $originalCookie = $_COOKIE;

    $_GET['__restore_probe_get'] = 'before-get';
    $_POST['__restore_probe_post'] = 'before-post';
    $_REQUEST['__restore_probe_request'] = 'before-request';
    $_SERVER['__RESTORE_PROBE_SERVER'] = 'before-server';
    $_COOKIE['__restore_probe_cookie'] = 'before-cookie';

    try {
        Route::get('/restore-superglobals', static fn () => response()->make(" 
                <html>
                <head></head>
                <body>
                    <form method='post' action='/restore-superglobals?source=query-value'>
                        <label for='name'>Your name</label>
                        <input id='name' type='text' name='name'>

                        <button type='submit'>Send</button>
                    </form>
                </body>
                </html>
            ")->cookie('restore_cookie', 'cookie-value'));

        Route::post('/restore-superglobals', static fn (Request $request): array => [
            'during_get' => $_GET['source'] ?? null,
            'during_post' => $_POST['name'] ?? null,
            'during_request_post' => $_REQUEST['name'] ?? null,
            'during_request_get' => $_REQUEST['source'] ?? null,
            'during_cookie' => $_COOKIE['restore_cookie'] ?? null,
            'during_server_probe' => $_SERVER['__RESTORE_PROBE_SERVER'] ?? null,
            'during_query_string' => $_SERVER['QUERY_STRING'] ?? null,
            'request_cookie' => $request->cookie('restore_cookie'),
        ]);

        $page = visit('/restore-superglobals');

        $page->fill('Your name', 'World');
        $page->click('Send');

        $page->assertSee('"during_get":"query-value"')
            ->assertSee('"during_post":"World"')
            ->assertSee('"during_request_post":"World"')
            ->assertSee('"during_request_get":"query-value"')
            ->assertSee('"during_cookie":"cookie-value"')
            ->assertSee('"during_server_probe":null')
            ->assertSee('"during_query_string":"source=query-value"')
            ->assertSee('"request_cookie":"cookie-value"');

        expect($_GET['__restore_probe_get'] ?? null)->toBe('before-get');
        expect($_POST['__restore_probe_post'] ?? null)->toBe('before-post');
        expect($_REQUEST['__restore_probe_request'] ?? null)->toBe('before-request');
        expect($_SERVER['__RESTORE_PROBE_SERVER'] ?? null)->toBe('before-server');
        expect($_COOKIE['__restore_probe_cookie'] ?? null)->toBe('before-cookie');
    } finally {
        $_GET = $originalGet;
        $_POST = $originalPost;
        $_REQUEST = $originalRequest;
        $_SERVER = $originalServer;
        $_COOKIE = $originalCookie;
    }
});
