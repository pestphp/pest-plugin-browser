<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\FileNotReadableException;
use Pest\Browser\Exceptions\FileTooLargeException;
use Pest\Browser\Support\FilePayload;

/**
 * Create a temporary file with the given contents and extension.
 */
function temporaryFile(string $contents, string $extension = ''): string
{
    $path = tempnam(sys_get_temp_dir(), 'pest_payload');

    if ($extension !== '') {
        unlink($path);
        $path .= '.'.$extension;
    }

    file_put_contents($path, $contents);

    return $path;
}

test('sends the file by value instead of by path', function (): void {
    $path = temporaryFile('hello world', 'txt');

    $payload = FilePayload::fromPath($path)->toArray();

    expect($payload)->toHaveKeys(['name', 'mimeType', 'buffer'])
        ->and($payload)->not->toHaveKey('localPaths')
        ->and($payload['buffer'])->not->toBe($path);

    unlink($path);
});

test('encodes the buffer as base64', function (): void {
    $path = temporaryFile('hello world', 'txt');

    $payload = FilePayload::fromPath($path)->toArray();

    expect($payload['buffer'])->toBe(base64_encode('hello world'))
        ->and(base64_decode($payload['buffer'], true))->toBe('hello world');

    unlink($path);
});

test('preserves binary contents without corruption', function (): void {
    $contents = '';
    for ($byte = 0; $byte < 256; $byte++) {
        $contents .= pack('C', $byte);
    }
    $contents .= "\x00\xC3\x28\xFF\xFE invalid utf-8 \r\n ";

    $path = temporaryFile($contents, 'bin');

    $payload = FilePayload::fromPath($path)->toArray();
    $decoded = (string) base64_decode($payload['buffer'], true);

    expect(hash('sha256', $decoded))->toBe(hash('sha256', $contents))
        ->and($decoded)->toBe($contents);

    unlink($path);
});

test('uses the file name rather than the full path', function (): void {
    $path = temporaryFile('contents', 'txt');

    expect(FilePayload::fromPath($path)->toArray()['name'])->toBe(basename($path));

    unlink($path);
});

test('guesses the mime type from the extension', function (): void {
    $path = temporaryFile('{}', 'json');

    expect(FilePayload::fromPath($path)->toArray()['mimeType'])->toBe('application/json');

    unlink($path);
});

test('falls back to a generic mime type for unknown extensions', function (): void {
    $path = temporaryFile('contents', 'unknownext');

    expect(FilePayload::fromPath($path)->toArray()['mimeType'])->toBe('application/octet-stream');

    unlink($path);
});

test('falls back to a generic mime type when there is no extension', function (): void {
    $path = temporaryFile('contents');

    expect(FilePayload::fromPath($path)->toArray()['mimeType'])->toBe('application/octet-stream');

    unlink($path);
});

test('fails when the file does not exist', function (): void {
    FilePayload::fromPath('/this/path/does/not/exist.txt');
})->throws(FileNotReadableException::class);

test('fails when the path is a directory', function (): void {
    FilePayload::fromPath(sys_get_temp_dir());
})->throws(FileNotReadableException::class);

test('fails when the file exceeds the playwright payload limit', function (): void {
    $path = temporaryFile('');

    $handle = fopen($path, 'wb');
    fseek($handle, 50 * 1024 * 1024);
    fwrite($handle, 'x');
    fclose($handle);

    try {
        expect(fn (): array => FilePayload::fromPath($path)->toArray())
            ->toThrow(FileTooLargeException::class);
    } finally {
        unlink($path);
    }
});
