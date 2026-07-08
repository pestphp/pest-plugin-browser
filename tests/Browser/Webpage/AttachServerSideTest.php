<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Playwright\Playwright;

// The defaults in tests/Pest.php (2000ms) are too short for round-trips
// that include form submit + server-side file processing on slower CI.
beforeEach(fn () => Playwright::setTimeout(10000));
afterEach(fn () => Playwright::setTimeout(2000));

it('forwards a single uploaded file to the Laravel request', function (): void {
    Route::post('/upload', function (Request $request): string {
        $file = $request->file('avatar');
        if ($file === null) {
            return 'NO_FILE';
        }

        return sprintf(
            '%s|%s|%d|%s',
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            $file->getSize(),
            $file->getContent(),
        );
    })->withoutMiddleware([ValidateCsrfToken::class]);

    Route::get('/form', fn (): string => '
        <form id="upload-form" method="POST" action="/upload" enctype="multipart/form-data">
            <input type="file" id="avatar" name="avatar">
        </form>
        <div id="result"></div>
        <script>
            document.getElementById("upload-form").addEventListener("submit", async (e) => {
                e.preventDefault();
                const fd = new FormData(e.target);
                const res = await fetch(e.target.action, { method: "POST", body: fd });
                document.getElementById("result").textContent = await res.text();
            });
        </script>
        <button id="submit" onclick="document.getElementById(\'upload-form\').dispatchEvent(new Event(\'submit\'))">Send</button>
    ');

    $tempFile = tempnam(sys_get_temp_dir(), 'pest_upload_');
    file_put_contents($tempFile, 'hello, server');

    // Note: client mime type comes from the browser (FormData picks a guess
    // based on the file extension, which is none for tempnam). Assert
    // separately on name + size + raw content; the MIME-type assertion is
    // skipped because it is environment-dependent.
    visit('/form')
        ->attach('#avatar', $tempFile)
        ->click('#submit')
        ->assertSee(basename($tempFile))
        ->assertSee('13') // bytes of "hello, server"
        ->assertSee('hello, server');

    unlink($tempFile);
});

it('preserves binary file content (no UTF-8 corruption)', function (): void {
    Route::post('/upload-binary', function (Request $request): string {
        $file = $request->file('payload');
        if ($file === null) {
            return 'NO_FILE';
        }

        return bin2hex($file->getContent());
    })->withoutMiddleware([ValidateCsrfToken::class]);

    Route::get('/form-binary', fn (): string => '
        <form id="upload-form" method="POST" action="/upload-binary" enctype="multipart/form-data">
            <input type="file" id="payload" name="payload">
        </form>
        <div id="result"></div>
        <script>
            document.getElementById("upload-form").addEventListener("submit", async (e) => {
                e.preventDefault();
                const fd = new FormData(e.target);
                const res = await fetch(e.target.action, { method: "POST", body: fd });
                document.getElementById("result").textContent = await res.text();
            });
        </script>
        <button id="submit" onclick="document.getElementById(\'upload-form\').dispatchEvent(new Event(\'submit\'))">Send</button>
    ');

    // Bytes that would corrupt under mb_* string operations: 0xFF, 0xFE,
    // 0xC0 (invalid UTF-8 lead byte), 0x00 (null), 0x0A (LF), 0x0D (CR).
    $binary = "\xFF\xFE\xC0\x00\x0A\x0D\x80\x81";
    $expectedHex = bin2hex($binary);

    $tempFile = tempnam(sys_get_temp_dir(), 'pest_binary_');
    file_put_contents($tempFile, $binary);

    visit('/form-binary')
        ->attach('#payload', $tempFile)
        ->click('#submit')
        ->assertSee($expectedHex);

    unlink($tempFile);
});

it('mixes file and non-file fields in the same multipart request', function (): void {
    Route::post('/upload-mixed', function (Request $request): string {
        $file = $request->file('avatar');
        $note = $request->input('note');

        return sprintf(
            'file=%s note=%s',
            $file !== null ? $file->getClientOriginalName() : 'NULL',
            $note ?? 'NULL',
        );
    })->withoutMiddleware([ValidateCsrfToken::class]);

    Route::get('/form-mixed', fn (): string => '
        <form id="upload-form" method="POST" action="/upload-mixed" enctype="multipart/form-data">
            <input type="text" id="note" name="note" value="">
            <input type="file" id="avatar" name="avatar">
        </form>
        <div id="result"></div>
        <script>
            document.getElementById("upload-form").addEventListener("submit", async (e) => {
                e.preventDefault();
                const fd = new FormData(e.target);
                const res = await fetch(e.target.action, { method: "POST", body: fd });
                document.getElementById("result").textContent = await res.text();
            });
        </script>
        <button id="submit" onclick="document.getElementById(\'upload-form\').dispatchEvent(new Event(\'submit\'))">Send</button>
    ');

    $tempFile = tempnam(sys_get_temp_dir(), 'pest_mixed_');
    file_put_contents($tempFile, 'x');

    visit('/form-mixed')
        ->fill('#note', 'remember-me')
        ->attach('#avatar', $tempFile)
        ->click('#submit')
        ->assertSee('remember-me')
        ->assertSee(basename($tempFile));

    unlink($tempFile);
});
