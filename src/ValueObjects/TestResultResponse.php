<?php

declare(strict_types=1);

namespace Pest\Browser\ValueObjects;

use Pest\Support\Arr;

/**
 * @internal
 */
final readonly class TestResultResponse
{
    private array $headers;

    private int $status;

    private string $url;

    /**
     * The test result response.
     */
    public function __construct(array $annotations)
    {
        /** @todo would be cleaner with collect() and Str facade */
        $annotation = Arr::last(array_filter($annotations, function ($annotation) {
            return Arr::get($annotation, 'type') === '_response';
        }));

        [
            'headers' => $this->headers,
            'status' => $this->status,
            'url' => $this->url
        ] = json_decode(Arr::get($annotation, 'description'), true);
    }

    /**
     * Get the test result response headers.
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get the test result response status.
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Get the test result response url.
     */
    public function url(): string
    {
        return $this->url;
    }
}
