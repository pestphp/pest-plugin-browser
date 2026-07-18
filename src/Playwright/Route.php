<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use RuntimeException;

final class Route
{
    use Concerns\InteractsWithPlaywright;

    /**
     * All requests indexed by guid
     *
     * @var array<string, callable(Route)>
     * */
    private static array $handlers = [];

    public function __construct(
        private readonly string $guid,
        private readonly Request $request
    ) {}

    public static function fromArray(array $params): self
    {
        return new self($params['guid'], Request::get($params['initializer']['request']['guid']));
    }

    /**
     * @param  callable(Route): bool  $handler
     */
    public static function registerRouteHandler(string $pattern, callable $handler): void
    {
        self::$handlers[self::regexFromPattern($pattern)] = $handler;
    }

    public static function handle(self $route): void
    {
        foreach (self::$handlers as $regex => $handler) {
            if (preg_match($regex, $route->request()->url) === 1) {
                $handler($route);

                return;
            }
        }
        throw new RuntimeException(
            sprintf('No route handler matched "%s".', $route->request()->url)
        );
    }

    public static function regexFromPattern(string $pattern): string
    {
        // Escape regex characters.
        $regex = preg_quote($pattern, '/');

        // Replace escaped globs with regex equivalents.
        $regex = str_replace('\*\*', '.*', $regex);      // ** => anything
        $regex = str_replace('\*', '[^\/]*', $regex);     // * => anything except /

        return '/^'.$regex.'$/';
    }

    public static function reset(): void
    {
        self::$handlers = [];
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function continue(array $params = ['isFallback' => true]): void
    {
        // Since this is called nested withing execute we don't want it to consume an actual response
        Client::instance()->executeWithoutResponse($this->guid, 'continue', $params);
    }

    public function abort(array $params = []): void
    {
        // Since this is called nested withing execute we don't want it to consume an actual response
        Client::instance()->executeWithoutResponse($this->guid, 'abort', $params);
    }

    public function fulfill(array $params = []): void
    {
        // Since this is called nested withing execute we don't want it to consume an actual response
        Client::instance()->executeWithoutResponse($this->guid, 'fulfill', $params);
    }
}
