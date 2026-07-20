<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\Browser\Exceptions\FileNotReadableException;
use Pest\Browser\Exceptions\FileTooLargeException;
use Symfony\Component\Mime\MimeTypes;

/**
 * A file to be uploaded, expressed as an inline Playwright payload.
 *
 * Playwright 1.61.0 and above refuse `localPaths` unless the client is
 * collocated with the server, a flag only set by the in-process and stdio
 * drivers — never by `run-server`. As this plugin always connects over a
 * websocket, files are sent by value instead of by path.
 *
 * @internal
 */
final readonly class FilePayload
{
    /**
     * The largest buffer Playwright accepts for an inline payload.
     */
    private const int MAX_SIZE_IN_BYTES = 50 * 1024 * 1024;

    /**
     * The MIME type used when the extension cannot be mapped to a known type.
     */
    private const string FALLBACK_MIME_TYPE = 'application/octet-stream';

    /**
     * Creates a new file payload instance.
     */
    private function __construct(
        private string $name,
        private string $mimeType,
        private string $contents,
    ) {
        //
    }

    /**
     * Create a payload from the file at the given path.
     */
    public static function fromPath(string $path): self
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new FileNotReadableException("The file [{$path}] does not exist or is not readable.");
        }

        $size = filesize($path);

        if ($size === false) {
            throw new FileNotReadableException("The size of the file [{$path}] could not be determined.");
        }

        if ($size > self::MAX_SIZE_IN_BYTES) {
            throw new FileTooLargeException(
                "The file [{$path}] exceeds the maximum upload size of 50 MB supported by Playwright."
            );
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new FileNotReadableException("The file [{$path}] could not be read.");
        }

        return new self(basename($path), self::guessMimeType($path), $contents);
    }

    /**
     * Return the payload in the shape expected by the Playwright protocol.
     *
     * The `buffer` field is typed as binary by the protocol, which is
     * base64 over the JSON channel.
     *
     * @return array{name: string, mimeType: string, buffer: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'mimeType' => $this->mimeType,
            'buffer' => base64_encode($this->contents),
        ];
    }

    /**
     * Guess the MIME type of the file from its extension.
     */
    private static function guessMimeType(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === '') {
            return self::FALLBACK_MIME_TYPE;
        }

        $mimeTypes = (new MimeTypes())->getMimeTypes($extension);

        return $mimeTypes[0] ?? self::FALLBACK_MIME_TYPE;
    }
}
