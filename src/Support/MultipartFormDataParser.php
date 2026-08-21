<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

/**
 * Parses a raw `multipart/form-data` request body into the "parameters" and
 * "files" arrays that `Symfony\Component\HttpFoundation\Request::create()`
 * expects, so requests sent by a real browser through a file input reach the
 * application the same way they would behind a normal PHP SAPI.
 *
 * @internal
 */
final class MultipartFormDataParser
{
    /**
     * Parses the given body, returning `[$parameters, $files]`.
     *
     * @return array{0: array<array-key, mixed>, 1: array<array-key, mixed>}
     */
    public static function parse(string $body, string $contentType): array
    {
        $parameters = [];
        $files = [];

        $boundary = self::boundary($contentType);

        if ($boundary === null) {
            return [$parameters, $files];
        }

        foreach (self::parts($body, $boundary) as [$headers, $content]) {
            $disposition = $headers['content-disposition'] ?? '';

            if (preg_match('/name="(?<name>[^"]*)"/', $disposition, $nameMatch) !== 1) {
                continue;
            }

            $path = self::path($nameMatch['name']);

            if (preg_match('/filename="(?<filename>[^"]*)"/', $disposition, $filenameMatch) === 1) {
                self::setFile($files, $path, $filenameMatch['filename'], $headers['content-type'] ?? null, $content);

                continue;
            }

            self::set($parameters, $path, $content);
        }

        return [$parameters, $files];
    }

    /**
     * Extracts the boundary token from a `multipart/form-data` content type header.
     */
    private static function boundary(string $contentType): ?string
    {
        if (preg_match('/boundary=(?:"(?<quoted>[^"]+)"|(?<bare>[^;]+))/', $contentType, $matches) !== 1) {
            return null;
        }

        $boundary = $matches['quoted'] !== '' ? $matches['quoted'] : $matches['bare'];

        return rtrim($boundary);
    }

    /**
     * Splits the raw body into `[headers, content]` pairs, one per part.
     *
     * @return list<array{0: array<string, string>, 1: string}>
     */
    private static function parts(string $body, string $boundary): array
    {
        $segments = explode('--'.$boundary, $body);

        // The first segment is the preamble before the first boundary, and the
        // last is whatever follows the closing `--boundary--`; neither is a part.
        array_shift($segments);
        array_pop($segments);

        $parts = [];

        foreach ($segments as $segment) {
            $segment = ltrim($segment, "\r\n");
            $headerEnd = strpos($segment, "\r\n\r\n");

            if ($headerEnd === false) {
                continue;
            }

            $content = substr($segment, $headerEnd + 4);

            if (str_ends_with($content, "\r\n")) {
                $content = substr($content, 0, -2);
            }

            $parts[] = [self::headers(substr($segment, 0, $headerEnd)), $content];
        }

        return $parts;
    }

    /**
     * Parses a block of `Header: value` lines into a lower-cased, associative array.
     *
     * @return array<string, string>
     */
    private static function headers(string $rawHeaders): array
    {
        $headers = [];

        foreach (explode("\r\n", $rawHeaders) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);

            $headers[strtolower(trim($key))] = trim($value);
        }

        return $headers;
    }

    /**
     * Splits a field name like `documents[a][]` into `['documents', 'a', '']`,
     * mirroring how PHP itself parses bracketed form field names.
     *
     * @return list<string>
     */
    private static function path(string $name): array
    {
        if (preg_match('/^(?<root>[^\[\]]+)(?<rest>(?:\[[^\]]*\])*)$/', $name, $matches) !== 1) {
            return [$name];
        }

        $path = [$matches['root']];

        if ($matches['rest'] !== '') {
            preg_match_all('/\[([^\]]*)\]/', $matches['rest'], $segments);

            array_push($path, ...$segments[1]);
        }

        return $path;
    }

    /**
     * Assigns `$value` at the nested location described by `$path`, appending
     * a new element whenever a path segment is the empty string (`foo[]`).
     *
     * @param  array<array-key, mixed>  $target
     * @param  list<string>  $path
     */
    private static function set(array &$target, array $path, mixed $value): void
    {
        $key = array_shift($path);

        if ($key === '') {
            if ($path === []) {
                $target[] = $value;

                return;
            }

            $target[] = [];
            self::set($target[array_key_last($target)], $path, $value);

            return;
        }

        if ($path === []) {
            $target[$key] = $value;

            return;
        }

        if (! isset($target[$key]) || ! is_array($target[$key])) {
            $target[$key] = [];
        }

        self::set($target[$key], $path, $value);
    }

    /**
     * Writes an uploaded file's content to a temporary file and assigns its
     * `$_FILES`-shaped descriptor at the nested location described by `$path`.
     *
     * @param  array<array-key, mixed>  $files
     * @param  list<string>  $path
     */
    private static function setFile(array &$files, array $path, string $filename, ?string $type, string $content): void
    {
        if ($filename === '') {
            self::set($files, $path, [
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => \UPLOAD_ERR_NO_FILE,
                'size' => 0,
            ]);

            return;
        }

        $tmpName = tempnam(sys_get_temp_dir(), 'pest_upload_');

        if ($tmpName === false) {
            return;
        }

        file_put_contents($tmpName, $content);

        self::set($files, $path, [
            'name' => $filename,
            'type' => $type ?? 'application/octet-stream',
            'tmp_name' => $tmpName,
            'error' => \UPLOAD_ERR_OK,
            'size' => strlen($content),
        ]);
    }
}
