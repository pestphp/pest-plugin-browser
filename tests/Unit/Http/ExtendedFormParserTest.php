<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Playwright\Playwright;

use function Pest\Laravel\withServerVariables;

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

it('matches content-type in server variables for URL-encoded form submissions', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/content-type'>
                <label for='name'>Your name</label>
                <input id='name' type='text' name='name'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/content-type', static function (Request $request): string {
        $matches = $request->server('CONTENT_TYPE') === $request->header('content-type');

        return $matches ? 'true' : 'false';
    });

    $page = visit('/');

    $page->fill('Your name', 'World');
    $page->click('Send');

    $page->assertSee('true');
});

it('overrides conflicting CONTENT_TYPE from server variables with request header', function (): void {
    withServerVariables(['CONTENT_TYPE' => 'text/plain']);

    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/content-type'>
                <label for='name'>Your name</label>
                <input id='name' type='text' name='name'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/content-type', static function (Request $request): string {
        $matches = $request->server('CONTENT_TYPE') === $request->header('content-type');

        return $matches ? 'true' : 'false';
    });

    $page = visit('/');

    $page->fill('Your name', 'World');
    $page->click('Send');

    $page->assertSee('true');
});

it('matches content-length in server variables for URL-encoded form submissions', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/content-length'>
                <label for='name'>Your name</label>
                <input id='name' type='text' name='name'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/content-length', static function (Request $request): string {
        $serverLength = (string) ($request->server('CONTENT_LENGTH') ?? '');
        $headerLength = (string) ($request->header('content-length') ?? '');
        $matches = $serverLength !== '' && $serverLength === $headerLength;

        return $matches ? 'true' : 'false';
    });

    Playwright::usingTimeout(15_000, function (): void {
        $page = visit('/');

        $page->fill('Your name', 'World');
        $page->click('Send');

        $page->assertSee('true');
    });
});

it('overrides conflicting CONTENT_LENGTH from server variables with request header', function (): void {
    withServerVariables(['CONTENT_LENGTH' => '1']);

    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/content-length'>
                <label for='name'>Your name</label>
                <input id='name' type='text' name='name'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/content-length', static function (Request $request): string {
        $serverLength = (string) ($request->server('CONTENT_LENGTH') ?? '');
        $headerLength = (string) ($request->header('content-length') ?? '');
        $matches = $serverLength !== '' && $serverLength === $headerLength;

        return $matches ? 'true' : 'false';
    });

    Playwright::usingTimeout(15_000, function (): void {
        $page = visit('/');

        $page->fill('Your name', 'World');
        $page->click('Send');

        $page->assertSee('true');
    });
});

it('overrides conflicting CONTENT_MD5 from server variables with request header', function (): void {
    withServerVariables(['CONTENT_MD5' => 'stale-md5']);

    Route::get('/content-md5', static fn (): string => "
        <html>
        <head></head>
        <body>
            <button id='send' type='button'>Send</button>
            <pre id='result'></pre>

            <script>
                document.getElementById('send').addEventListener('click', async () => {
                    const response = await fetch('/content-md5', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Content-MD5': 'abc123',
                        },
                        body: 'name=World',
                    });

                    document.getElementById('result').textContent = await response.text();
                });
            </script>
        </body>
        </html>
    ");

    Route::post('/content-md5', static function (Request $request): string {
        $serverMd5 = (string) ($request->server('CONTENT_MD5') ?? '');
        $headerMd5 = (string) ($request->header('content-md5') ?? '');

        $payload = [
            'server_md5' => $serverMd5,
            'header_md5' => $headerMd5,
            'md5_matches_header' => $serverMd5 !== '' && $serverMd5 === $headerMd5,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        assert($json !== false);

        return '<pre id="payload">'.$json.'</pre>';
    });

    $page = visit('/content-md5');

    $page->click('Send');

    $page->assertSee('"server_md5":"abc123"')
        ->assertSee('"header_md5":"abc123"')
        ->assertSee('"md5_matches_header":true');
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

    Playwright::usingTimeout(15_000, function (): void {
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
});

it('matches content-type in server variables when uploading files', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/content-type' enctype='multipart/form-data'>
                <label for='file1'>A file</label>
                <input id='file1' type='file' name='file1'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/content-type', static function (Request $request): string {
        $matches = $request->server('CONTENT_TYPE') === $request->header('content-type');

        return $matches ? 'true' : 'false';
    });

    Playwright::usingTimeout(15_000, function (): void {
        visit('/')
            ->attach('A file', fixture('lorem-ipsum.txt'))
            ->click('Send')
            ->assertSee('true');
    });
});

it('matches content-length in server variables when uploading files', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/content-length' enctype='multipart/form-data'>
                <label for='file1'>A file</label>
                <input id='file1' type='file' name='file1'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/content-length', static function (Request $request): string {
        $serverLength = (string) ($request->server('CONTENT_LENGTH') ?? '');
        $headerLength = (string) ($request->header('content-length') ?? '');
        $matches = $serverLength !== '' && $serverLength === $headerLength;

        return $matches ? 'true' : 'false';
    });

    Playwright::usingTimeout(15_000, function (): void {
        visit('/')
            ->attach('A file', fixture('lorem-ipsum.txt'))
            ->click('Send')
            ->assertSee('true');
    });
});

it('preserves custom server variables while synchronizing upload content headers', function (): void {
    withServerVariables([
        'X_CUSTOM_UPLOAD_FLAG' => 'enabled',
        'CONTENT_TYPE' => 'text/plain',
        'CONTENT_LENGTH' => '1',
    ]);

    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/server-vars' enctype='multipart/form-data'>
                <label for='file1'>A file</label>
                <input id='file1' type='file' name='file1'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/server-vars', static function (Request $request): string {
        $custom = (string) ($request->server('X_CUSTOM_UPLOAD_FLAG') ?? '');
        $serverType = (string) ($request->server('CONTENT_TYPE') ?? '');
        $headerType = (string) ($request->header('content-type') ?? '');
        $serverLength = (string) ($request->server('CONTENT_LENGTH') ?? '');
        $headerLength = (string) ($request->header('content-length') ?? '');

        $payload = [
            'custom_preserved' => $custom === 'enabled',
            'type_matches_header' => $serverType === $headerType,
            'length_matches_header' => $serverLength === $headerLength,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        assert($json !== false);

        return '<pre id="payload">'.$json.'</pre>';
    });

    Playwright::usingTimeout(15_000, function (): void {
        visit('/')
            ->attach('A file', fixture('lorem-ipsum.txt'))
            ->click('Send')
            ->assertSee('"custom_preserved":true')
            ->assertSee('"type_matches_header":true')
            ->assertSee('"length_matches_header":true');
    });
});

it('hydrates request superglobals during kernel handling', function (): void {
    Route::get('/superglobals', static fn () => response()->make(" 
        <html>
        <head></head>
        <body>
            <form method='post' action='/superglobals?source=query-value'>
                <label for='name'>Your name</label>
                <input id='name' type='text' name='name'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ")->cookie('super_cookie', 'cookie-value'));

    Route::post('/superglobals', static fn (Request $request) => response()->json([
        'post_name' => $_POST['name'] ?? null,
        'request_name' => $_REQUEST['name'] ?? null,
        'get_source' => $_GET['source'] ?? null,
        'cookie_super' => $_COOKIE['super_cookie'] ?? null,
        'query_string' => $_SERVER['QUERY_STRING'] ?? null,
        'request_query_source' => $request->query('source'),
        'request_cookie_super' => $request->cookie('super_cookie'),
        'type_has_form' => str_contains((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/x-www-form-urlencoded'),
        'length_matches' => (string) ($_SERVER['CONTENT_LENGTH'] ?? '') === (string) ($request->header('content-length') ?? ''),
    ]));

    $page = visit('/superglobals');

    $page->fill('Your name', 'World');
    $page->click('Send');

    $page->assertSee('"post_name":"World"')
        ->assertSee('"request_name":"World"')
        ->assertSee('"get_source":"query-value"')
        ->assertSee('"cookie_super":"cookie-value"')
        ->assertSee('"query_string":"source=query-value"')
        ->assertSee('"request_query_source":"query-value"')
        ->assertSee('"request_cookie_super":"cookie-value"')
        ->assertSee('"type_has_form":true')
        ->assertSee('"length_matches":true');
});

it('keeps content server variables empty on GET requests', function (): void {
    Route::get('/server-content-vars', static function (Request $request): string {
        $payload = [
            'server_content_type' => $request->server('CONTENT_TYPE'),
            'server_content_length' => $request->server('CONTENT_LENGTH'),
            'header_content_type' => $request->header('content-type'),
            'header_content_length' => $request->header('content-length'),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        assert($json !== false);

        return '<pre id="payload">'.$json.'</pre>';
    });

    visit('/server-content-vars')
        ->assertSee('"server_content_type":null')
        ->assertSee('"server_content_length":null')
        ->assertSee('"header_content_type":null')
        ->assertSee('"header_content_length":null');
});

it('preserves multipart boundary and numeric content-length in server variables', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' action='/content-metadata' enctype='multipart/form-data'>
                <label for='file1'>A file</label>
                <input id='file1' type='file' name='file1'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/content-metadata', static function (Request $request): string {
        $serverType = (string) ($request->server('CONTENT_TYPE') ?? '');
        $headerType = (string) ($request->header('content-type') ?? '');
        $serverLength = (string) ($request->server('CONTENT_LENGTH') ?? '');
        $headerLength = (string) ($request->header('content-length') ?? '');

        $payload = [
            'type_matches' => $serverType === $headerType,
            'length_matches' => $serverLength === $headerLength,
            'has_boundary' => str_contains($serverType, 'boundary='),
            'length_is_numeric' => $serverLength !== '' && ctype_digit($serverLength),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        assert($json !== false);

        return '<pre id="payload">'.$json.'</pre>';
    });

    Playwright::usingTimeout(15_000, function (): void {
        visit('/')
            ->attach('A file', fixture('lorem-ipsum.txt'))
            ->click('Send')
            ->assertSee('"type_matches":true')
            ->assertSee('"length_matches":true')
            ->assertSee('"has_boundary":true')
            ->assertSee('"length_is_numeric":true');
    });
});

it('applies MAX_FILE_SIZE multipart validation error', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <input type='hidden' name='MAX_FILE_SIZE' value='1'>

                <label for='document'>Document</label>
                <input id='document' type='file' name='document'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/form', static function (Request $request): string {
        $document = $request->file('document');

        return '
            <html>
            <head></head>
            <body>
                <p>Has file: '.($document !== null ? 'yes' : 'no').'</p>
                <p>Error: '.($document?->getError() ?? 'none').'</p>
            </body>
            </html>
        ';
    });

    Playwright::usingTimeout(15_000, function (): void {
        $page = visit('/');

        $page->attach('Document', fixture('lorem-ipsum.txt'));
        $page->submit();

        $page->assertSee('Has file: yes')
            ->assertSee('Error: '.UPLOAD_ERR_FORM_SIZE);
    });
});

it('applies UPLOAD_ERR_NO_FILE when no file is selected', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='document'>Document</label>
                <input id='document' type='file' name='document'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/form', static function (Request $request): string {
        $file = $request->files->get('document');

        $hasNoFileError = $file === null
            || (
                $file instanceof Illuminate\Http\UploadedFile
                && $file->getError() === UPLOAD_ERR_NO_FILE
            );

        return '
            <html>
            <head></head>
            <body>
                <p>No file error: '.($hasNoFileError ? 'yes' : 'no').'</p>
            </body>
            </html>
        ';
    });

    Playwright::usingTimeout(15_000, function (): void {
        $page = visit('/');
        $page->submit();

        $page->assertSee('No file error: yes');
    });
});

it('applies UPLOAD_ERR_INI_SIZE for oversized multipart upload', function (): void {
    $http = Pest\Browser\ServerManager::instance()->http();
    assert($http instanceof Pest\Browser\Drivers\LaravelHttpServer);

    $http->setExtendedFormParser(new Pest\Browser\Http\ExtendedFormParser(
        maxInputVars: 1000,
        uploadMaxFilesize: 1,
        maxFileUploads: 20,
    ));

    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='document'>Document</label>
                <input id='document' type='file' name='document'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/form', static function (Request $request): string {
        $document = $request->file('document');

        return '
            <html>
            <head></head>
            <body>
                <p>Error: '.($document?->getError() ?? 'none').'</p>
            </body>
            </html>
        ';
    });

    $oversizedFile = tempnam(sys_get_temp_dir(), 'multipart-oversized-');
    assert($oversizedFile !== false);

    try {
        $written = file_put_contents($oversizedFile, 'AB');
        assert($written !== false);

        Playwright::usingTimeout(15_000, function () use ($oversizedFile): void {
            $page = visit('/');
            $page->attach('Document', $oversizedFile);
            $page->submit();

            $page->assertSee('Error: '.UPLOAD_ERR_INI_SIZE);
        });
    } finally {
        $http->setExtendedFormParser(Pest\Browser\Http\ExtendedFormParser::fromIni());
        unlink($oversizedFile);
    }
});

it('enforces upload limits using byte size for multibyte file contents', function (): void {
    $http = Pest\Browser\ServerManager::instance()->http();
    assert($http instanceof Pest\Browser\Drivers\LaravelHttpServer);

    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='document'>Document</label>
                <input id='document' type='file' name='document'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/form', static function (Request $request): string {
        $document = $request->file('document');

        return '
            <html>
            <head></head>
            <body>
                <p>Error: '.($document?->getError() ?? 'none').'</p>
                <p>Size: '.($document?->getSize() ?? 'none').'</p>
            </body>
            </html>
        ';
    });

    $multibyteContents = str_repeat('¢', 3);
    $charLength = mb_strlen($multibyteContents);
    $byteLength = mb_strlen($multibyteContents, '8bit');

    expect($charLength)->toBe(3);
    expect($byteLength)->toBe(6);

    $multibyteFile = tempnam(sys_get_temp_dir(), 'multipart-multibyte-');
    assert($multibyteFile !== false);

    try {
        $written = file_put_contents($multibyteFile, $multibyteContents);
        assert($written !== false);

        $http->setExtendedFormParser(new Pest\Browser\Http\ExtendedFormParser(
            maxInputVars: 1000,
            uploadMaxFilesize: $byteLength - 1,
            maxFileUploads: 20,
        ));

        Playwright::usingTimeout(15_000, function () use ($multibyteFile): void {
            $page = visit('/');
            $page->attach('Document', $multibyteFile);
            $page->submit();

            $page->assertSee('Error: '.UPLOAD_ERR_INI_SIZE);
        });

        $http->setExtendedFormParser(new Pest\Browser\Http\ExtendedFormParser(
            maxInputVars: 1000,
            uploadMaxFilesize: $byteLength,
            maxFileUploads: 20,
        ));

        Playwright::usingTimeout(15_000, function () use ($multibyteFile, $byteLength): void {
            $page = visit('/');
            $page->attach('Document', $multibyteFile);
            $page->submit();

            $page->assertSee('Error: 0')
                ->assertSee('Size: '.$byteLength);
        });
    } finally {
        $http->setExtendedFormParser(Pest\Browser\Http\ExtendedFormParser::fromIni());
        unlink($multibyteFile);
    }
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

    Playwright::usingTimeout(15000, function () use ($expectedPdfSize): void {
        $page = visit('/');

        $page->attach('PDF file', fixture('example.pdf'));
        $page->submit();

        $page->assertSee('Name: example.pdf')
            ->assertSee('Extension: pdf')
            ->assertSee('Valid: yes')
            ->assertSee('Expected size: '.$expectedPdfSize)
            ->assertSee('Size: '.$expectedPdfSize);
    });
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

it('parses linear nested indexed multipart fields without merging values', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='extra'>Extra</label>
                <input id='extra' type='text' name='extra'>

                <label for='data-0-field1'>Data 0 Field 1</label>
                <input id='data-0-field1' type='text' name='data[0][field1]'>

                <label for='data-0-field2'>Data 0 Field 2</label>
                <input id='data-0-field2' type='text' name='data[0][field2]'>

                <label for='data-1-field1'>Data 1 Field 1</label>
                <input id='data-1-field1' type='text' name='data[1][field1]'>

                <label for='data-1-field2'>Data 1 Field 2</label>
                <input id='data-1-field2' type='text' name='data[1][field2]'>

                <label for='matrix-1'>Matrix 1</label>
                <input id='matrix-1' type='text' name='matrix[][]'>

                <label for='matrix-2'>Matrix 2</label>
                <input id='matrix-2' type='text' name='matrix[][]'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/form', static function (Request $request): string {
        $payload = json_encode($request->all(), JSON_UNESCAPED_UNICODE);
        assert($payload !== false);

        return '
            <html>
            <head></head>
            <body>
                <pre id="payload">'.$payload.'</pre>
            </body>
            </html>
        ';
    });

    Playwright::usingTimeout(15_000, function (): void {
        $page = visit('/');

        $page->fill('Extra', 'Test');
        $page->fill('Data 0 Field 1', '1');
        $page->fill('Data 0 Field 2', '1');
        $page->fill('Data 1 Field 1', '2');
        $page->fill('Data 1 Field 2', '0');
        $page->fill('Matrix 1', 'A');
        $page->fill('Matrix 2', 'B');
        $page->submit();

        $expectedPayload = json_encode([
            'extra' => 'Test',
            'data' => [
                [
                    'field1' => '1',
                    'field2' => '1',
                ],
                [
                    'field1' => '2',
                    'field2' => '0',
                ],
            ],
            'matrix' => [
                ['A'],
                ['B'],
            ],
        ], JSON_UNESCAPED_UNICODE);
        assert($expectedPayload !== false);

        $page->assertSee($expectedPayload)
            ->assertSee('"extra":"Test"')
            ->assertSee('"data":[{"field1":"1","field2":"1"},{"field1":"2","field2":"0"}]')
            ->assertSee('"matrix":[["A"],["B"]]');
    });
});

it('preserves sparse numeric indexes in multipart nested fields', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='data-2-field'>Data 2 Field</label>
                <input id='data-2-field' type='text' name='data[2][field]'>

                <label for='data-5-field'>Data 5 Field</label>
                <input id='data-5-field' type='text' name='data[5][field]'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/form', static function (Request $request): string {
        $payload = json_encode($request->all(), JSON_UNESCAPED_UNICODE);
        assert($payload !== false);

        return '<pre id="payload">'.$payload.'</pre>';
    });

    Playwright::usingTimeout(15_000, function (): void {
        $page = visit('/');

        $page->fill('Data 2 Field', 'A');
        $page->fill('Data 5 Field', 'B');
        $page->submit();

        $page->assertSee('"data":{"2":{"field":"A"},"5":{"field":"B"}}');
    });
});

it('overwrites duplicate explicit multipart keys with last value', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='field2-first'>Field2 first</label>
                <input id='field2-first' type='text' name='data[0][field2]'>

                <label for='field2-second'>Field2 second</label>
                <input id='field2-second' type='text' name='data[0][field2]'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/form', static fn (Request $request): string => '<p>Field2: '.$request->input('data.0.field2').'</p>');

    Playwright::usingTimeout(15_000, function (): void {
        $page = visit('/');

        $page->fill('Field2 first', 'first');
        $page->fill('Field2 second', 'second');
        $page->submit();

        $page->assertSee('Field2: second');
    });
});

it('handles mixed multipart payload with files and nested arrays', function (): void {
    Route::get('/', static fn (): string => "
        <html>
        <head></head>
        <body>
            <form method='post' enctype='multipart/form-data' action='/form'>
                <label for='extra'>Extra</label>
                <input id='extra' type='text' name='extra'>

                <label for='data-0-field1'>Data 0 Field 1</label>
                <input id='data-0-field1' type='text' name='data[0][field1]'>

                <label for='flags-1'>Flag 1</label>
                <input id='flags-1' type='text' name='flags[][]'>

                <label for='flags-2'>Flag 2</label>
                <input id='flags-2' type='text' name='flags[][]'>

                <label for='document'>Document</label>
                <input id='document' type='file' name='document'>

                <button type='submit'>Send</button>
            </form>
        </body>
        </html>
    ");

    Route::post('/form', static function (Request $request): string {
        $payload = [
            'fields' => $request->all(),
            'file' => [
                'name' => $request->file('document')?->getClientOriginalName(),
                'error' => $request->file('document')?->getError(),
            ],
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        assert($json !== false);

        return '<pre id="payload">'.$json.'</pre>';
    });

    Playwright::usingTimeout(15_000, function (): void {
        $page = visit('/');

        $page->fill('Extra', 'Mix');
        $page->fill('Data 0 Field 1', '10');
        $page->fill('Flag 1', 'X');
        $page->fill('Flag 2', 'Y');
        $page->attach('Document', fixture('lorem-ipsum.txt'));
        $page->submit();

        $page->assertSee('"extra":"Mix"')
            ->assertSee('"data":[{"field1":"10"}]')
            ->assertSee('"flags":[["X"],["Y"]]')
            ->assertSee('"name":"lorem-ipsum.txt"')
            ->assertSee('"error":0');
    });
});
