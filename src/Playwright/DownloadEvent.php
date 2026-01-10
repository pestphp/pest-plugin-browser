<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final readonly class DownloadEvent
{
    public function __construct(
        public string $pageGuid,
        public string $url,
        public string $suggestedFilename,
        public string $artifactGuid,
    ) {}

    /**
     * Creates a DownloadEvent from a Playwright response, or returns null if not a download event.
     *
     * @param  array<string, mixed>  $response
     */
    public static function fromResponse(array $response): ?self
    {
        if (($response['method'] ?? null) !== 'download') {
            return null;
        }

        $pageGuid = $response['guid'] ?? null;
        $params = $response['params'] ?? [];
        $params = is_array($params) ? $params : [];
        $artifact = $params['artifact'] ?? [];
        $artifact = is_array($artifact) ? $artifact : [];

        if (! is_string($pageGuid)
            || ! is_string($params['url'] ?? null)
            || ! is_string($params['suggestedFilename'] ?? null)
            || ! is_string($artifact['guid'] ?? null)) {
            return null;
        }

        return new self(
            $pageGuid,
            $params['url'],
            $params['suggestedFilename'],
            $artifact['guid'],
        );
    }
}
