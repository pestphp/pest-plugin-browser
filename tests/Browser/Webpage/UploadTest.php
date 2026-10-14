<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

it('nests the files of a multipart form the way PHP does', function (): void {
    Route::get('/', fn (): string => '
        <form method="post" action="/upload" enctype="multipart/form-data">
            <input type="file" id="first" name="documents[]">
            <input type="file" id="second" name="documents[]">
            <input type="file" id="avatar" name="profile[avatar]">
            <button type="submit">Upload</button>
        </form>
    ');
    Route::post('/upload', fn (Request $request): string => sprintf(
        'documents %s, avatar %s',
        implode('+', array_map(fn (UploadedFile $file): string => (string) file_get_contents($file->getPathname()), $request->file('documents'))),
        (string) file_get_contents($request->file('profile.avatar')->getPathname()),
    ));

    $files = [];
    foreach (['first', 'second', 'avatar'] as $content) {
        $files[$content] = (string) tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($files[$content], $content);
    }

    visit('/')
        ->attach('#first', $files['first'])
        ->attach('#second', $files['second'])
        ->attach('#avatar', $files['avatar'])
        ->click('Upload')
        ->assertSee('documents first+second, avatar avatar');

    array_map(unlink(...), $files);
});
