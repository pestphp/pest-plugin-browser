<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

final class Request
{
    /**
     * All requests indexed by guid
     *
     * @var array<string, Request>
     * */
    private static array $requests = [];

    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public string $guid,
        public string $frameGuid,
        public string $url,
        public string $resourceType,
        public string $method,
        public array $headers,
        public bool $isNavigationRequest,
    ) {}

    public static function fromArray(array $params): self
    {
        $headers = [];
        foreach ($params['initializer']['headers'] as $header) {
            $headers[$header['name']] = $header['value'];
        }

        return new self(
            $params['guid'],
            frameGuid: $params['initializer']['frame']['guid'],
            url: $params['initializer']['url'],
            resourceType: $params['initializer']['resourceType'],
            method: $params['initializer']['method'],
            headers: $headers,
            isNavigationRequest: $params['initializer']['isNavigationRequest'],
        );
    }

    public static function register(self $request): void
    {
        self::$requests[$request->guid] = $request;
    }

    public static function get(string $guid): self
    {
        return self::$requests[$guid];
    }

    public static function reset(): void
    {
        self::$requests = [];
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }
}
