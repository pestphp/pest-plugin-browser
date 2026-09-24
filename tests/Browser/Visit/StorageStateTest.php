<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

test('may seed cookies via withStorageState before visiting', function (): void {
    Route::get('/', fn (): string => '<html><body>Storage state</body></html>');

    visit('/')
        ->withStorageState([
            'cookies' => [[
                'name' => 'session_token',
                'value' => 'abc123',
                'domain' => '127.0.0.1',
                'path' => '/',
            ]],
        ])
        ->assertScript("document.cookie.includes('session_token=abc123')", true);
});

test('may seed cookies via withStorageStateFromFile before visiting', function (): void {
    Route::get('/', fn (): string => '<html><body>Storage state from file</body></html>');

    $path = tempnam(sys_get_temp_dir(), 'storage-state').'.json';

    file_put_contents($path, json_encode([
        'cookies' => [[
            'name' => 'from_file_token',
            'value' => 'xyz789',
            'domain' => '127.0.0.1',
            'path' => '/',
        ]],
        'origins' => [],
    ]));

    try {
        visit('/')
            ->withStorageStateFromFile($path)
            ->assertScript("document.cookie.includes('from_file_token=xyz789')", true);
    } finally {
        unlink($path);
    }
});

test('withStorageStateFromFile throws when the file does not exist', function (): void {
    visit('/')->withStorageStateFromFile('/nonexistent/path/storage-state.json');
})->throws(InvalidArgumentException::class, 'Storage state file not found');

test('withStorageStateFromFile throws for malformed JSON', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'storage-state').'.json';
    file_put_contents($path, 'not valid json');

    try {
        visit('/')->withStorageStateFromFile($path);
    } finally {
        unlink($path);
    }
})->throws(InvalidArgumentException::class);

test('withStorageStateFromFile throws when the file contains a literal JSON null', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'storage-state').'.json';
    file_put_contents($path, 'null');

    try {
        visit('/')->withStorageStateFromFile($path);
    } finally {
        unlink($path);
    }
})->throws(InvalidArgumentException::class, 'Storage state JSON must not be null');
