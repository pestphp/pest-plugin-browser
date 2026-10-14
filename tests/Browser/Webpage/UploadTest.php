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
