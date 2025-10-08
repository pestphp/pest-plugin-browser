<?php

declare(strict_types=1);

namespace Pest\Browser\Http;

use Amp\Http\Server\Request as AmpRequest;
use InvalidArgumentException;

/**
 * Derived work from the MultipartParser in ReactPHP.
 * Modified to support AMP Request and resemble the $_POST and $_FILES superglobals
 *
 * @internal
 *
 * @see https://github.com/reactphp/http/blob/3.x/src/Io/MultipartParser.php
 */
final class MultipartParser
{
    /**
     * @var array{"$_POST": array<array-key, mixed[]|string>, "$_FILES": array<array-key, array{tmp_file: string, error: int, name: string, type: string, size: int}[]|array{tmp_file: string, error: int, name: string, type: string, size: int}>}
     */
    private array $superglobals = ['$_POST' => [], '$_FILES' => []];

    private ?int $maxFileSize = null;

    /**
     * Based on $maxInputVars and $maxFileUploads
     */
    private int $maxMultipartBodyParts;

    /**
     * ini setting "max_input_vars"
     *
     * Assume PHP default of 1000 here.
     *
     * @link http://php.net/manual/en/info.configuration.php#ini.max-input-vars
     */
    private int $maxInputVars;

    /**
     * ini setting "max_input_nesting_level"
     *
     * Assume PHP's default of 64 here.
     *
     * @link http://php.net/manual/en/info.configuration.php#ini.max-input-nesting-level
     */
    private int $maxInputNestingLevel;

    /**
     * ini setting "upload_max_filesize"
     */
    private int|float $uploadMaxFilesize;

    /**
     * ini setting "max_file_uploads"
     *
     * Additionally, setting "file_uploads = off" effectively sets this to zero.
     */
    private int $maxFileUploads;

    private int $multipartBodyPartCount = 0;

    private int $postCount = 0;

    private int $filesCount = 0;

    private int $emptyCount = 0;

    private int|false $cursor = 0;

    public function __construct(
        ?string $uploadMaxFilesize = null,
        ?int $maxFileUploads = null,
    ) {
        $this->maxInputVars = (int) ini_get('max_input_vars');
        $this->maxInputNestingLevel = (int) ini_get('max_input_nesting_level');

        if ($uploadMaxFilesize === null) {
            $uploadMaxFilesize = (string) ini_get('upload_max_filesize');
        }

        $this->uploadMaxFilesize = self::iniSizeToBytes($uploadMaxFilesize);
        $this->maxFileUploads = $maxFileUploads ?? (ini_get('file_uploads') === '' ? 0 : (int) ini_get('max_file_uploads'));

        $this->maxMultipartBodyParts = $this->maxInputVars + $this->maxFileUploads;
    }

    /**
     * @return array{array<mixed[]|string>, array<array{tmp_file: string, error: int, name: string, type: string, size: int}[]|array{tmp_file: string, error: int, name: string, type: string, size: int}>}
     */
    public function parse(AmpRequest $request, string $body): array
    {
        $contentType = $request->getHeader('content-type') ?? '';
        if (preg_match('/boundary="?(.*?)"?$/', $contentType, $matches) === false) {
            return [[], []];
        }

        $this->parseBody('--'.$matches[1], $body);

        $superglobals = $this->superglobals;
        $this->superglobals = ['$_POST' => [], '$_FILES' => []];
        $this->multipartBodyPartCount = 0;
        $this->cursor = 0;
        $this->postCount = 0;
        $this->filesCount = 0;
        $this->emptyCount = 0;
        $this->maxFileSize = null;

        return array_values($superglobals);
    }

    /**
     * @see https://github.com/reactphp/http/blob/3.x/src/Io/IniUtil.php
     */
    private static function iniSizeToBytes(string $size): int|float
    {
        if (is_numeric($size)) {
            return (int) $size;
        }

        $suffix = strtoupper(substr($size, -1));
        $strippedSize = substr($size, 0, -1);

        if (! is_numeric($strippedSize)) {
            throw new InvalidArgumentException("$size is not a valid ini size");
        }

        if ($strippedSize <= 0) {
            throw new InvalidArgumentException("Expect $size to be higher isn't zero or lower");
        }

        if ($suffix === 'K') {
            return $strippedSize * 1024;
        }
        if ($suffix === 'M') {
            return $strippedSize * 1024 * 1024;
        }
        if ($suffix === 'G') {
            return $strippedSize * 1024 * 1024 * 1024;
        }
        if ($suffix === 'T') {
            return $strippedSize * 1024 * 1024 * 1024 * 1024;
        }

        return (int) $size;
    }

    private function parseBody(string $boundary, string $buffer): void
    {
        $len = strlen($boundary);

        // ignore everything before initial boundary (SHOULD be empty)
        $this->cursor = strpos($buffer, $boundary."\r\n");

        while ($this->cursor !== false) {
            // search following boundary (preceded by newline)
            // ignore last if not followed by boundary (SHOULD end with "--")
            $this->cursor += $len + 2;
            $end = strpos($buffer, "\r\n".$boundary, $this->cursor);
            if ($end === false) {
                break;
            }

            // parse one part and continue searching for next
            $this->parsePart(substr($buffer, $this->cursor, $end - $this->cursor));
            $this->cursor = $end;

            if (++$this->multipartBodyPartCount > $this->maxMultipartBodyParts) {
                break;
            }
        }
    }

    private function parsePart(string $chunk): void
    {
        $pos = strpos($chunk, "\r\n\r\n");
        if ($pos === false) {
            return;
        }

        $headers = $this->parseHeaders(substr($chunk, 0, $pos));
        $body = substr($chunk, $pos + 4);

        if (! isset($headers['content-disposition'])) {
            return;
        }

        $name = $this->getParameterFromHeader($headers['content-disposition'], 'name');
        if ($name === null) {
            return;
        }

        $filename = $this->getParameterFromHeader($headers['content-disposition'], 'filename');
        if ($filename !== null) {
            $this->parseFile(
                $name,
                $filename,
                $headers['content-type'][0] ?? null,
                $body
            );
        } else {
            $this->parsePost($name, $body);
        }
    }

    private function parseFile(string $name, string $filename, ?string $contentType, string $contents): void
    {
        $file = $this->parseUploadedFile($filename, $contentType, $contents);
        if ($file === null) {
            return;
        }

        $this->superglobals['$_FILES'] = $this->extractPost(
            $this->superglobals['$_FILES'],
            $name,
            $file,
        );
    }

    /**
     * @return array{tmp_file: string, error: int, name: string, type: string, size: int}|null
     */
    private function parseUploadedFile(string $filename, ?string $contentType, string $contents): ?array
    {
        $contentType ??= 'application/octet-stream';
        $size = strlen($contents);

        // no file selected (zero size and empty filename)
        if ($size === 0 && $filename === '') {
            // ignore excessive number of empty file uploads
            if (++$this->emptyCount + $this->filesCount > $this->maxInputVars) {
                return null;
            }

            return [
                'tmp_name' => '',
                'error' => UPLOAD_ERR_NO_FILE,
                'name' => $filename,
                'type' => $contentType,
                'size' => $size,
            ];
        }

        // ignore excessive number of file uploads
        if (++$this->filesCount > $this->maxFileUploads) {
            return null;
        }

        // file exceeds "upload_max_filesize" ini setting
        if ($size > $this->uploadMaxFilesize) {
            return [
                'tmp_name' => '',
                'error' => UPLOAD_ERR_INI_SIZE,
                'name' => $filename,
                'type' => $contentType,
                'size' => $size,
            ];
        }

        // file exceeds MAX_FILE_SIZE value
        if ($this->maxFileSize !== null && $size > $this->maxFileSize) {
            return [
                'tmp_name' => '',
                'error' => UPLOAD_ERR_FORM_SIZE,
                'name' => $filename,
                'type' => $contentType,
                'size' => $size,
            ];
        }

        $tempFileName = tempnam(sys_get_temp_dir(), 'php');
        file_put_contents($tempFileName, $contents);

        return [
            'tmp_name' => $tempFileName,
            'error' => UPLOAD_ERR_OK,
            'name' => $filename,
            'type' => $contentType,
            'size' => $size,
        ];
    }

    private function parsePost(string $name, string $value): void
    {
        // ignore excessive number of post fields
        if (++$this->postCount > $this->maxInputVars) {
            return;
        }

        $this->superglobals['$_POST'] = $this->extractPost(
            $this->superglobals['$_POST'],
            $name,
            $value
        );

        if (strtoupper($name) === 'MAX_FILE_SIZE') {
            $this->maxFileSize = (int) $value;

            if ($this->maxFileSize === 0) {
                $this->maxFileSize = null;
            }
        }
    }

    /**
     * @return array<string, string[]>
     */
    private function parseHeaders(string $header): array
    {
        $headers = [];

        foreach (explode("\r\n", trim($header)) as $line) {
            $parts = explode(':', $line, 2);
            if (! isset($parts[1])) {
                continue;
            }

            $key = strtolower(trim($parts[0]));
            $values = explode(';', $parts[1]);
            $values = array_map('trim', $values);
            $headers[$key] = $values;
        }

        return $headers;
    }

    /**
     * @param  string[]  $header
     */
    private function getParameterFromHeader(array $header, string $parameter): ?string
    {
        foreach ($header as $part) {
            if (preg_match('/'.$parameter.'="?(.*?)"?$/', $part, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * @template TArray of array<array-key, mixed[]|mixed>
     *
     * @param  TArray  $postFields
     * @return TArray
     */
    private function extractPost(array $postFields, string $key, mixed $value): array
    {
        $chunks = explode('[', $key);
        if (count($chunks) === 1) {
            $postFields[$key] = $value;

            /** @phpstan-ignore-next-line I don't know why it changes the value */
            return $postFields;
        }

        // ignore this key if maximum nesting level is exceeded
        if (isset($chunks[$this->maxInputNestingLevel])) {
            return $postFields;
        }

        $chunkKey = rtrim($chunks[0], ']');
        $parent = &$postFields;
        for ($i = 1; isset($chunks[$i]); $i++) {
            $previousChunkKey = $chunkKey;

            if ($previousChunkKey === '') {
                /** @phpstan-ignore-next-line Array manipulation over mixed */
                $parent[] = [];
                /** @phpstan-ignore-next-line Array manipulation over mixed */
                end($parent);
                /** @phpstan-ignore-next-line Array manipulation over mixed */
                $parent = &$parent[key($parent)];
            } else {
                /** @phpstan-ignore-next-line Array manipulation over mixed */
                if (! isset($parent[$previousChunkKey]) || ! is_array($parent[$previousChunkKey])) {
                    /** @phpstan-ignore-next-line Array manipulation over mixed */
                    $parent[$previousChunkKey] = [];
                }
                $parent = &$parent[$previousChunkKey];
            }

            $chunkKey = rtrim($chunks[$i], ']');
        }

        if ($chunkKey === '') {
            /** @phpstan-ignore-next-line More array manipulation over mixed */
            $parent[] = $value;
        } else {
            /** @phpstan-ignore-next-line More array manipulation over mixed */
            $parent[$chunkKey] = $value;
        }

        return $postFields;
    }
}
