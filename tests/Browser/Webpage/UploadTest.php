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

it('hands the application a binary file byte for byte', function (): void {
    Route::get('/', fn (): string => '
        <form method="post" action="/upload" enctype="multipart/form-data">
            <input type="file" id="avatar" name="avatar">
            <button type="submit">Upload</button>
        </form>
    ');
    Route::post('/upload', function (Request $request): string {
        $file = $request->file('avatar');

        return sprintf(
            'received %d bytes, sha256 %s',
            (int) $file->getSize(),
            hash('sha256', (string) file_get_contents($file->getPathname())),
        );
    });

    // PNG magic, then bytes that are not valid UTF-8. A multibyte string
    // function re-encodes the whole body and replaces these with "?".
    $bytes = "\x89PNG\r\n\x1a\n\xff\xfe\xc0\x80\x81";

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, $bytes);

    visit('/')
        ->attach('#avatar', $tempFile)
        ->click('Upload')
        ->assertSee(sprintf('received %d bytes, sha256 %s', mb_strlen($bytes, '8bit'), hash('sha256', $bytes)));

    unlink($tempFile);
});

it('nests files whose field names use brackets, as PHP does', function (): void {
    Route::get('/', fn (): string => '
        <form method="post" action="/upload" enctype="multipart/form-data">
            <input type="file" id="first" name="files[]">
            <input type="file" id="second" name="files[]">
            <input type="file" id="cv" name="docs[cv]">
            <button type="submit">Upload</button>
        </form>
    ');
    Route::post('/upload', function (Request $request): string {
        $names = array_map(fn ($file): string => $file->getClientOriginalName(), $request->file('files') ?? []);

        return sprintf(
            'files: %s; cv: %s',
            implode('+', $names),
            $request->file('docs.cv')?->getClientOriginalName() ?? 'none',
        );
    });

    $first = tempnam(sys_get_temp_dir(), 'first');
    $second = tempnam(sys_get_temp_dir(), 'second');
    $cv = tempnam(sys_get_temp_dir(), 'cv');
    foreach ([$first, $second, $cv] as $path) {
        file_put_contents($path, 'content');
    }

    $page = visit('/');
    $page->attach('#first', $first)
        ->attach('#second', $second)
        ->attach('#cv', $cv)
        ->click('Upload')
        ->assertSee(sprintf('files: %s+%s; cv: %s', basename($first), basename($second), basename($cv)));

    foreach ([$first, $second, $cv] as $path) {
        unlink($path);
    }
});

it('cleans an upload replaced by a later nested field', function (): void {
    Route::get('/', fn (): string => '
        <form method="post" action="/upload" enctype="multipart/form-data">
            <input type="file" id="first" name="files">
            <input type="file" id="avatar" name="files[avatar]">
            <button type="submit">Upload</button>
        </form>
    ');
    Route::post('/upload', function (Request $request): string {
        return sprintf('avatar: %s', $request->file('files.avatar')?->getClientOriginalName() ?? 'none');
    });

    $firstContent = 'first-'.uniqid('', true);
    $secondContent = 'second-'.uniqid('', true);
    $first = tempnam(sys_get_temp_dir(), 'first');
    $second = tempnam(sys_get_temp_dir(), 'second');
    file_put_contents($first, $firstContent);
    file_put_contents($second, $secondContent);

    try {
        visit('/')
            ->attach('#first', $first)
            ->attach('#avatar', $second)
            ->click('Upload')
            ->assertSee(sprintf('avatar: %s', basename($second)));

        $leakedUploads = [];
        foreach (scandir(sys_get_temp_dir()) ?: [] as $name) {
            if (! str_starts_with($name, 'pest-upload-')) {
                continue;
            }

            $content = @file_get_contents(sys_get_temp_dir().DIRECTORY_SEPARATOR.$name);
            if ($content === $firstContent || $content === $secondContent) {
                $leakedUploads[] = $name;
            }
        }

        expect($leakedUploads)->toBe([]);
    } finally {
        unlink($first);
        unlink($second);
    }
});
