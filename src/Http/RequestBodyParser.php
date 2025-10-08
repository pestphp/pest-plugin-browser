<?php

declare(strict_types=1);

namespace Pest\Browser\Http;

use Amp\Http\Server\Request as AmpRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @internal
 *
 * @see https://github.com/reactphp/http/blob/3.x/src/Middleware/RequestBodyParserMiddleware.php
 */
final class RequestBodyParser
{
    private MultipartParser $multipart;

    public function __construct(?string $uploadMaxFilesize = null, ?int $maxFileUploads = null)
    {
        $this->multipart = new MultipartParser($uploadMaxFilesize, $maxFileUploads);
    }

    /**
     * @return array{array<array-key, mixed[]|string>, array<array-key, UploadedFile[]|UploadedFile>}
     */
    public function parseForm(AmpRequest $request, string $body): array
    {
        $type = strtolower($request->getHeader('Content-Type') ?? '');
        [$type] = explode(';', $type);

        if ($type === 'application/x-www-form-urlencoded') {
            return $this->parseFormUrlencoded($body);
        }

        if ($type === 'multipart/form-data') {
            return $this->multipart->parse($request, $body);
        }

        return [[], []];
    }

    /**
     * @return array{array<array-key, mixed[]|string>, array<array-key, UploadedFile[]|UploadedFile>}
     */
    private function parseFormUrlencoded(string $body): array
    {
        // parse string into array structure
        // ignore warnings due to excessive data structures (max_input_vars and max_input_nesting_level)
        $ret = [];
        @parse_str($body, $ret);

        return [$ret, []];
    }
}
