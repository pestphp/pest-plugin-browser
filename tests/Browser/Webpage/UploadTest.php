<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

it('hands the application the files and the fields of a multipart form', function (): void {
    Route::get('/', fn (): string => '
        <form method="post" action="/upload" enctype="multipart/form-data">
            <input type="text" name="label" value="my avatar">
            <input type="text" name="tags[]" value="one">
            <input type="text" name="tags[]" value="two">
            <input type="file" id="avatar" name="avatar">
            <button type="submit">Upload</button>
        </form>
    ');
    Route::post('/upload', function (Request $request): string {
        $file = $request->file('avatar');

        return sprintf(
            'received %s (%d bytes, %s) labelled "%s" with tags %s, valid: %s',
            $file->getClientOriginalName(),
            $file->getSize(),
            (string) file_get_contents($file->getPathname()),
            $request->input('label'),
            implode('+', $request->input('tags')),
            $file->isValid() ? 'yes' : 'no',
        );
    });

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'test content');

    $page = visit('/');
    $page->attach('#avatar', $tempFile)
        ->click('Upload')
        ->assertSee(sprintf('received %s (12 bytes, test content) labelled "my avatar" with tags one+two, valid: yes', basename($tempFile)));

    unlink($tempFile);
});

it('keeps the bytes of an uploaded file that are not valid UTF-8', function (): void {
    Route::get('/', fn (): string => '
        <form method="post" action="/upload" enctype="multipart/form-data">
            <input type="file" id="avatar" name="avatar">
            <button type="submit">Upload</button>
        </form>
    ');
    Route::post('/upload', fn (Request $request): string => 'received '.bin2hex(
        (string) file_get_contents($request->file('avatar')->getPathname()),
    ));

    // The first bytes of a PNG, which no UTF-8 decoder accepts.
    $bytes = "\x89PNG\r\n\x1a\n\x00\x00\x00\x0dIHDR\xff\xfe\x80";
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, $bytes);

    visit('/')
        ->attach('#avatar', $tempFile)
        ->click('Upload')
        ->assertSee('received '.bin2hex($bytes));

    unlink($tempFile);
});
