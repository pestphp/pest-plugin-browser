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

it('flushes scoped container bindings between requests', function (): void {
    // A scoped() binding is released only by Application::forgetScopedInstances(). Since this
    // server reuses one container across a test's requests, it must flush per request — otherwise
    // the instance resolved in the first request leaks into the second (and would behave like a
    // singleton), unlike FPM or Octane. Each request renders the resolved object's id; they differ.
    app()->scoped('scoped-probe', fn (): object => new stdClass);
    Route::get('/scoped-probe', fn (): string => spl_object_hash(app('scoped-probe')));

    $first = visit('/scoped-probe')->content();
    $second = visit('/scoped-probe')->content();

    expect($first)->not->toBe($second);
});
