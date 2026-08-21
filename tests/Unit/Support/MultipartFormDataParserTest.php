<?php

declare(strict_types=1);

use Pest\Browser\Support\MultipartFormDataParser;

function multipartBody(string $boundary, array $lines): string
{
    return implode("\r\n", $lines)
        ."\r\n--{$boundary}--\r\n";
}

it('parses plain text fields', function (): void {
    $boundary = 'Boundary123';
    $body = multipartBody($boundary, [
        "--{$boundary}",
        'Content-Disposition: form-data; name="type"',
        '',
        'municipal_valuation',
        "--{$boundary}",
        'Content-Disposition: form-data; name="notes"',
        '',
        'multi-line' . "\r\n" . 'value',
    ]);

    [$parameters, $files] = MultipartFormDataParser::parse($body, "multipart/form-data; boundary={$boundary}");

    expect($parameters)->toBe([
        'type' => 'municipal_valuation',
        'notes' => "multi-line\r\nvalue",
    ])->and($files)->toBe([]);
});

it('parses a single uploaded file into a Symfony-compatible descriptor', function (): void {
    $boundary = 'Boundary123';
    $content = "%PDF-1.4\nfake pdf bytes";
    $body = multipartBody($boundary, [
        "--{$boundary}",
        'Content-Disposition: form-data; name="type"',
        '',
        'municipal_valuation',
        "--{$boundary}",
        'Content-Disposition: form-data; name="file"; filename="valuation.pdf"',
        'Content-Type: application/pdf',
        '',
        $content,
    ]);

    [$parameters, $files] = MultipartFormDataParser::parse($body, "multipart/form-data; boundary={$boundary}");

    expect($parameters)->toBe(['type' => 'municipal_valuation'])
        ->and($files)->toHaveKey('file')
        ->and($files['file']['name'])->toBe('valuation.pdf')
        ->and($files['file']['type'])->toBe('application/pdf')
        ->and($files['file']['error'])->toBe(\UPLOAD_ERR_OK)
        ->and($files['file']['size'])->toBe(strlen($content))
        ->and(file_get_contents($files['file']['tmp_name']))->toBe($content);
});

it('marks an empty file input as UPLOAD_ERR_NO_FILE', function (): void {
    $boundary = 'Boundary123';
    $body = multipartBody($boundary, [
        "--{$boundary}",
        'Content-Disposition: form-data; name="avatar"; filename=""',
        'Content-Type: application/octet-stream',
        '',
        '',
    ]);

    [, $files] = MultipartFormDataParser::parse($body, "multipart/form-data; boundary={$boundary}");

    expect($files['avatar']['error'])->toBe(\UPLOAD_ERR_NO_FILE)
        ->and($files['avatar']['tmp_name'])->toBe('');
});

it('collects repeated bracketed fields into a list, mirroring PHP\'s own form parsing', function (): void {
    $boundary = 'Boundary123';
    $body = multipartBody($boundary, [
        "--{$boundary}",
        'Content-Disposition: form-data; name="tags[]"',
        '',
        'roof',
        "--{$boundary}",
        'Content-Disposition: form-data; name="tags[]"',
        '',
        'damp',
        "--{$boundary}",
        'Content-Disposition: form-data; name="documents[]"; filename="a.png"',
        'Content-Type: image/png',
        '',
        'aaa',
        "--{$boundary}",
        'Content-Disposition: form-data; name="documents[]"; filename="b.png"',
        'Content-Type: image/png',
        '',
        'bbb',
    ]);

    [$parameters, $files] = MultipartFormDataParser::parse($body, "multipart/form-data; boundary={$boundary}");

    expect($parameters)->toBe(['tags' => ['roof', 'damp']])
        ->and($files['documents'])->toHaveCount(2)
        ->and($files['documents'][0]['name'])->toBe('a.png')
        ->and($files['documents'][1]['name'])->toBe('b.png');
});

it('returns empty parameters and files when the content type has no boundary', function (): void {
    [$parameters, $files] = MultipartFormDataParser::parse('irrelevant', 'multipart/form-data');

    expect($parameters)->toBe([])->and($files)->toBe([]);
});
