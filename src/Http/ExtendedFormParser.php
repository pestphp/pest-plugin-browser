<?php

declare(strict_types=1);

namespace Pest\Browser\Http;

use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

use Amp\Http\Server\FormParser\BufferedFile;
use Amp\Http\Server\FormParser\Form;
use Amp\Http\Server\Request as AmpRequest;
use Illuminate\Http\UploadedFile;

final class ExtendedFormParser
{
    private int $filesCount = 0;

    private int $emptyCount = 0;

    private ?int $maxFileSize = null;

    public function __construct(
        private readonly int $maxInputVars = 1000,
        private readonly int|float $uploadMaxFilesize = 0,
        private readonly int $maxFileUploads = 20,
    ) {}

    public static function fromIni(): self
    {
        $maxInputVarsRaw = ini_get('max_input_vars');
        $maxInputVars = (int) ($maxInputVarsRaw !== false ? $maxInputVarsRaw : 1000);
        $uploadMaxFilesize = self::iniSizeToBytes((string) ini_get('upload_max_filesize'));
        $maxFileUploadsRaw = ini_get('max_file_uploads');
        $maxFileUploads = (int) ($maxFileUploadsRaw !== false ? $maxFileUploadsRaw : 20);

        return new self($maxInputVars, $uploadMaxFilesize, $maxFileUploads);
    }

    /**
     * @return array{array<int|string, mixed>, array<int|string, mixed>}
     */
    public function parseMultipart(AmpRequest $request): array
    {
        $this->filesCount = 0;
        $this->emptyCount = 0;
        $this->maxFileSize = null;

        $form = Form::fromRequest($request);
        $values = $form->getValues();

        $maxFileSize = $values['MAX_FILE_SIZE'][0] ?? null;
        if (is_string($maxFileSize) && is_numeric($maxFileSize)) {
            $parsedMaxFileSize = (int) $maxFileSize;

            if ($parsedMaxFileSize > 0) {
                $this->maxFileSize = $parsedMaxFileSize;
            }
        }

        $parameters = [];
        foreach ($values as $field => $entries) {
            foreach ($entries as $entry) {
                $this->setFieldValue($parameters, $field, $entry);
            }
        }

        $files = [];
        foreach ($form->getFiles() as $field => $entries) {
            foreach ($entries as $entry) {
                $parsedFile = $this->parseUploadedFile($entry);

                if ($parsedFile === null) {
                    continue;
                }

                $this->setFieldValue($files, $field, $parsedFile);
            }
        }

        return [$parameters, $files];
    }

    private static function iniSizeToBytes(string $size): int|float
    {
        if (is_numeric($size)) {
            return (int) $size;
        }

        $suffix = mb_strtoupper(mb_substr($size, -1));
        $strippedSize = mb_substr($size, 0, -1);

        if (! is_numeric($strippedSize)) {
            return 0;
        }

        $value = (float) $strippedSize;

        return match ($suffix) {
            'K' => $value * 1024,
            'M' => $value * 1024 * 1024,
            'G' => $value * 1024 * 1024 * 1024,
            'T' => $value * 1024 * 1024 * 1024 * 1024,
            default => (int) $size,
        };
    }

    /**
     * @return UploadedFile|array{name: string, type: string, tmp_name: string, error: int, size: int}|null
     */
    private function parseUploadedFile(BufferedFile $entry): UploadedFile|array|null
    {
        $contents = $entry->getContents();
        $filename = $entry->getName();
        $contentType = $entry->getMimeType();

        if ($contentType === '') {
            $contentType = 'application/octet-stream';
        }

        $size = mb_strlen($contents, '8bit');

        if ($size === 0 && $filename === '') {
            if (++$this->emptyCount + $this->filesCount > $this->maxInputVars) {
                return null;
            }

            return [
                'name' => $filename,
                'type' => $contentType,
                'tmp_name' => '',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0,
            ];
        }

        if (++$this->filesCount > $this->maxFileUploads) {
            return null;
        }

        if ($size > $this->uploadMaxFilesize) {
            return [
                'name' => $filename,
                'type' => $contentType,
                'tmp_name' => '',
                'error' => UPLOAD_ERR_INI_SIZE,
                'size' => $size,
            ];
        }

        if ($this->maxFileSize !== null && $size > $this->maxFileSize) {
            return [
                'name' => $filename,
                'type' => $contentType,
                'tmp_name' => '',
                'error' => UPLOAD_ERR_FORM_SIZE,
                'size' => $size,
            ];
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'pest-browser-upload-');
        if ($tempPath === false) {
            return [
                'name' => $filename,
                'type' => $contentType,
                'tmp_name' => '',
                'error' => UPLOAD_ERR_CANT_WRITE,
                'size' => $size,
            ];
        }

        $written = file_put_contents($tempPath, $contents);
        if ($written === false) {
            return [
                'name' => $filename,
                'type' => $contentType,
                'tmp_name' => '',
                'error' => UPLOAD_ERR_CANT_WRITE,
                'size' => $size,
            ];
        }

        return new UploadedFile(
            $tempPath,
            $filename !== '' ? $filename : 'upload',
            $contentType,
            UPLOAD_ERR_OK,
            true,
        );
    }

    /**
     * @param  array<int|string, mixed>  $target
     */
    private function setFieldValue(array &$target, string $field, mixed $value): void
    {
        $segments = $this->fieldSegments($field);

        if ($segments === []) {
            return;
        }

        $this->setNestedFieldValue($target, $segments, $value);
    }

    /**
     * @return list<string>
     */
    private function fieldSegments(string $field): array
    {
        if (! str_contains($field, '[')) {
            return [$field];
        }

        $segments = [];
        $head = mb_strstr($field, '[', true);

        if ($head !== false && $head !== '') {
            $segments[] = $head;
        }

        preg_match_all('/\[([^\]]*)\]/', $field, $matches);

        foreach ($matches[1] as $segment) {
            $segments[] = $segment;
        }

        return $segments;
    }

    /**
     * @param  array<int|string, mixed>  $target
     * @param  list<string>  $segments
     */
    private function setNestedFieldValue(array &$target, array $segments, mixed $value): void
    {
        $segment = array_shift($segments);

        if ($segment === null) {
            return;
        }

        if ($segments === []) {
            if ($segment === '') {
                $target[] = $value;

                return;
            }

            if (! array_key_exists($segment, $target)) {
                $target[$segment] = $value;

                return;
            }

            if (! is_array($target[$segment])) {
                $target[$segment] = [$target[$segment]];
            }

            $target[$segment][] = $value;

            return;
        }

        if ($segment === '') {
            $target[] = [];

            $lastKey = array_key_last($target);
            if (! is_array($target[$lastKey])) {
                return;
            }

            $this->setNestedFieldValue($target[$lastKey], $segments, $value);

            return;
        }

        if (! isset($target[$segment]) || ! is_array($target[$segment])) {
            $target[$segment] = [];
        }

        $this->setNestedFieldValue($target[$segment], $segments, $value);
    }
}
