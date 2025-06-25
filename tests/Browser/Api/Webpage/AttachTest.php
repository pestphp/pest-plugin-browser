<?php

declare(strict_types=1);

it('may attach a file to a file input', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="file" id="avatar" name="avatar">
        </form>
    ');

    $page = visit('/');

    // Create a temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'test content');

    $page->attach('avatar', $tempFile);

    // Check that the file is attached
    $fileName = basename($tempFile);
    expect($page->script('() => document.querySelector("input[name=avatar]").files[0].name'))->toBe($fileName);

    // Clean up
    unlink($tempFile);
});

it('may attach a file to a file input using an id selector', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="file" id="avatar" name="avatar">
        </form>
    ');

    $page = visit('/');

    // Create a temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'test content');

    $page->attach('#avatar', $tempFile);

    // Check that the file is attached
    $fileName = basename($tempFile);
    expect($page->script('() => document.querySelector("#avatar").files[0].name'))->toBe($fileName);

    // Clean up
    unlink($tempFile);
});
