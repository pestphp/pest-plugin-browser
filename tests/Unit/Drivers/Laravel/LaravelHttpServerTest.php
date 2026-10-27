<?php

declare(strict_types=1);

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Pest\Laravel\withServerVariables;
use function Pest\Laravel\withUnencryptedCookie;

// These assertions pin the bound IP, so make the host explicit rather than
// inheriting whatever an earlier test file configured globally.
beforeEach(function (): void {
    pest()->browser()->withHost(null);
});

it('rewrites the URLs on JS files', function (): void {
    // Use a unique filename so parallel workers sharing `public/` don't race on `app.js`.
    $filename = 'app-'.uniqid('', true).'.js';
    $filepath = public_path($filename);

    @file_put_contents(
        $filepath,
        <<<'JS'
        console.log('Hello http://localhost');
        JS,
    );

    try {
        $page = visit('/'.$filename);

        $page->assertSee('http://127.0.0.1')
            ->assertDontSee('http://localhost');
    } finally {
        @unlink($filepath);
    }
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

it('does not re-send a cookie queued in an earlier request', function (): void {
    // Without flush the second response would re-send the queued cookie.
    Route::get('/queue-cookie', function (): string {
        Cookie::queue('queued-probe', 'first-request', 5);

        return 'queued';
    })->middleware(AddQueuedCookiesToResponse::class);
    Route::get('/no-cookie', fn (): string => 'plain')->middleware(AddQueuedCookiesToResponse::class);

    $queued = [];
    Event::listen(RequestHandled::class, function (RequestHandled $event) use (&$queued): void {
        foreach ($event->response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === 'queued-probe') {
                $queued[] = $event->request->path();
            }
        }
    });

    visit('/queue-cookie')->assertSee('queued');
    visit('/no-cookie')->assertSee('plain');

    expect($queued)->toBe(['queue-cookie']);
});

it('resolves scoped bindings fresh on every request', function (): void {
    // Without flush the second request would get the first request's instance, like a singleton.
    $resolved = 0;
    app()->scoped('scoped-probe', function () use (&$resolved): ArrayObject {
        $resolved++;

        return new ArrayObject();
    });
    Route::get('/scoped-probe', function (): string {
        $probe = app('scoped-probe');
        $probe[] = 'hit';

        return 'hits: '.count($probe);
    });

    visit('/scoped-probe')->assertSee('hits: 1');
    visit('/scoped-probe')->assertSee('hits: 1');

    expect($resolved)->toBe(2);
});

it('keeps a scoped binding the test registered', function (): void {
    // A mock or instance bound by the test must reach every request, as it does with `$this->get()`.
    app()->scoped('scoped-probe', fn (): ArrayObject => new ArrayObject(['source' => 'container']));
    app()->instance('scoped-probe', new ArrayObject(['source' => 'test']));
    Route::get('/scoped-probe', fn (): string => 'source: '.app('scoped-probe')['source']);

    visit('/scoped-probe')->assertSee('source: test');
    visit('/scoped-probe')->assertSee('source: test');
});

it('resolves scoped bindings fresh behind facades', function (): void {
    // Facades cache what they resolve, so without flush the context of one request would reach the next.
    Route::get('/context/add', function (): string {
        Context::add('probe', 'first request');

        return 'added';
    });
    Route::get('/context/read', fn (): string => 'probe: '.Context::get('probe', 'none'));

    visit('/context/add')->assertSee('added');
    visit('/context/read')->assertSee('probe: none');
});

it('forgets scoped instances when the request throws', function (): void {
    // Without exception handling the kernel throws, and the failed request's instances must go too.
    $this->withoutExceptionHandling();
    app()->scoped('scoped-probe', fn (): ArrayObject => new ArrayObject());
    Route::get('/scoped-probe', function (): never {
        $probe = app('scoped-probe');
        $probe[] = 'hit';

        throw new RuntimeException('The request failed.');
    });

    expect(function (): void {
        visit('/scoped-probe')->assertPathIs('/scoped-probe');
    })->toThrow(RuntimeException::class, 'The request failed.');

    expect(app('scoped-probe'))->toHaveCount(0);
});

it('forgets scoped instances when a streamed response throws', function (): void {
    // The stream callback runs after the kernel has handled the request, so its instances must go even when it fails.
    app()->scoped('scoped-probe', fn (): ArrayObject => new ArrayObject());
    Route::get('/scoped-stream', fn (): StreamedResponse => response()->stream(function (): void {
        $probe = app('scoped-probe');
        $probe[] = 'hit';

        throw new RuntimeException('The stream failed.');
    }));

    visit('/scoped-stream')->assertSee('500');

    expect(app('scoped-probe'))->toHaveCount(0);
});

it('forgets scoped instances when terminating the request throws', function (): void {
    // Terminate runs after the kernel has handled the request, so its instances must go even when it fails.
    app()->scoped('scoped-probe', fn (): ArrayObject => new ArrayObject());
    app()->terminating(function (): void {
        $probe = app('scoped-probe');
        $probe[] = 'hit';

        throw new RuntimeException('Terminating failed.');
    });
    Route::get('/scoped-probe', fn (): string => 'handled');

    visit('/scoped-probe')->assertSee('500');

    expect(app('scoped-probe'))->toHaveCount(0);
});
