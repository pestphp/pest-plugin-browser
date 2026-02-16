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

it('parse a URL-encoded body', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/form'>
                <label for='name'>Your name</label>
                <input id='name' type='text' name='name'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");
    Route::post('/form', static fn (Request $request): string => "
        <html>
        <head></head>
        <body>
            <h1>Hello {$request->post('name')}</h1>
        </body>
        </html>
    ");

    $page = visit('/');
    $page->assertSee('Your name');

    $page->fill('Your name', 'World');
    $page->click('Send');

    $page->assertSee('Hello World');
});

it('parse a multipart body with files', function (): void {
    Route::get('favicon.ico', static fn (): string => '');
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='name'>Your name</label>
                <input id='name' type='text' name='name'>

                <label for='file1'>Your text file</label>
                <input id='file1' type='file' name='file1'>

                <label for='file2'>Your binary file</label>
                <input id='file2' type='file' name='file2'>

                <label for='file3'>Your empty file</label>
                <input id='file3' type='file' name='file3'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");
    Route::post('/form', static fn (Request $request): string => "
        <html>
        <head></head>
        <body>
            <h1>Hello {$request->post('name')}</h1>
            <p>Text file: {$request->file('file1')?->getClientOriginalName()}</p>
            <p>Binary file: {$request->file('file2')?->getClientOriginalName()}</p>
            <p>Empty file: {$request->file('file3')?->getClientOriginalName()}</p>
        </body>
        </html>
    ");

    $page = visit('/');
    $page->assertSee('Your name');

    $page->fill('Your name', 'World');
    $page->attach('Your text file', fixture('lorem-ipsum.txt'));
    $page->attach('Your binary file', fixture('example.pdf'));
    $page->submit();

    $page->assertSee('Hello World');
    $page->assertSee('Text file: lorem-ipsum.txt');
    $page->assertSee('Binary file: example.pdf');
    $page->assertSee('Empty file: ');
});

it('validates multipart pdf upload metadata', function (): void {
    Route::get('favicon.ico', static fn (): string => '');
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='pdf-file'>PDF file</label>
                <input id='pdf-file' type='file' name='pdf_file'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    $expectedPdfSize = filesize(fixture('example.pdf'));
    assert($expectedPdfSize !== false);

    Route::post('/form', static function (Request $request) use ($expectedPdfSize): string {
        $pdf = $request->file('pdf_file');
        $name = $pdf?->getClientOriginalName() ?? '';
        $extension = $pdf?->getClientOriginalExtension() ?? '';
        $valid = $pdf?->isValid() ? 'yes' : 'no';
        $size = (string) ($pdf?->getSize() ?? '');

        return "
            <html>
            <head></head>
            <body>
                <p>Name: $name</p>
                <p>Extension: $extension</p>
                <p>Valid: $valid</p>
                <p>Size: $size</p>
                <p>Expected size: $expectedPdfSize</p>
            </body>
            </html>
        ";
    });

    $page = visit('/');

    $page->attach('PDF file', fixture('example.pdf'));
    $page->submit();

    $page->assertSee('Name: example.pdf')
        ->assertSee('Extension: pdf')
        ->assertSee('Valid: yes')
        ->assertSee('Expected size: '.$expectedPdfSize)
        ->assertSee('Size: '.$expectedPdfSize);
});

it('parse a multipart body with nested fields', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='person-first-name'>Your first name</label>
                <input id='person-first-name' type='text' name='person[first_name]'>

                <label for='person-last-name'>Your last name</label>
                <input id='person-last-name' type='text' name='person[last_name]'>

                <label for='children-name-1'>Child 1</label>
                <input id='children-name-1' type='text' name='children[]'>

                <label for='children-name-2'>Child 2</label>
                <input id='children-name-2' type='text' name='children[]'>

                <label for='children-name-3'>Child 3</label>
                <input id='children-name-3' type='text' name='children[]'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");
    Route::post('/form', static function (Request $request): string {
        $childNames = implode(', ', (array) $request->input('children'));

        return "
            <html>
            <head></head>
            <body>
                <h1>Hello {$request->input('person.first_name')} {$request->input('person.last_name')}</h1>
                <p>and $childNames</p>
            </body>
            </html>
        ";
    });

    $page = visit('/');

    $page->fill('Your first name', 'Jane');
    $page->fill('Your last name', 'Doe');
    $page->fill('Child 2', 'Johnathan');
    $page->fill('Child 3', 'Jamie');
    $page->fill('Child 1', 'John');
    $page->submit();

    $page->assertSee('Hello Jane Doe')
        ->assertSee('and John, Johnathan, Jamie');
});
