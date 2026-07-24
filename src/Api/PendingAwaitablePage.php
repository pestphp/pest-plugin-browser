<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\ColorScheme;
use Pest\Browser\Enums\Device;
use Pest\Browser\Playwright\InitScript;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Support\ComputeUrl;

/**
 * @mixin Webpage|AwaitableWebpage
 */
final class PendingAwaitablePage
{
    /**
     * The webpage instance that will be returned when the page is visited.
     */
    private ?AwaitableWebpage $waitablePage = null;

    /**
     * Creates a new pending awaitable page instance.
     *
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        private readonly BrowserType $browserType,
        private readonly Device $device,
        private readonly string $url,
        private readonly array $options,
        private readonly array $routes = [],
    ) {
        //
    }

    /**
     * Creates the actual visit page instance, and calls the given method on it.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->waitablePage ??= $this->createAwaitablePage();

        // @phpstan-ignore-next-line
        return $this->waitablePage->{$name}(...$arguments);
    }

    /**
     * @param  callable(Route): bool  $handler
     */
    public function withRoute(string $pattern, callable $handler): self
    {
        return new self(
            $this->browserType,
            $this->device,
            $this->url,
            $this->options,
            [
                ...$this->routes,
                $pattern => $handler,
            ],
        );
    }

    /**
     * Sets the color scheme to dark mode.
     */
    public function inDarkMode(): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'colorScheme' => ColorScheme::DARK->value,
            ...$this->options,
        ], $this->routes);
    }

    /**
     * Sets the color scheme to light mode.
     */
    public function inLightMode(): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'colorScheme' => ColorScheme::LIGHT->value,
            ...$this->options,
        ], $this->routes);
    }

    /**
     * Allows you to set a different locale, timezone, and location for the page.
     */
    public function from(): From
    {
        return new From(
            $this->browserType,
            $this->device,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Allows you to set a different device for the page.
     */
    public function on(): On
    {
        return new On(
            $this->browserType,
            $this->device,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the locale for the page.
     */
    public function withLocale(string $locale): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'locale' => $locale,
            ...$this->options,
        ], $this->routes);
    }

    /**
     * Sets the userAgent for the page.
     */
    public function withUserAgent(string $userAgent): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'userAgent' => $userAgent,
            ...$this->options,
        ], $this->routes);
    }

    /**
     * Sets the host for the server.
     */
    public function withHost(string $host): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'host' => $host,
            ...$this->options,
        ], $this->routes);
    }

    /**
     * Sets the timezone for the page.
     */
    public function withTimezone(string $timezone): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'timezoneId' => $timezone,
            ...$this->options,
        ], $this->routes);
    }

    /**
     * Sets the geolocation for the page.
     */
    public function geolocation(float $latitude, float $longitude): self
    {
        $geolocation = ['latitude' => $latitude, 'longitude' => $longitude];

        return new self($this->browserType, $this->device, $this->url, [
            'geolocation' => $geolocation,
            'permissions' => ['geolocation'],
            ...$this->options,
        ], $this->routes);
    }

    /**
     * Creates the webpage instance.
     */
    private function createAwaitablePage(): AwaitableWebpage
    {
        $options = $this->options;
        $host = $this->extractHost($options);

        return $this->withTemporaryHost($host, fn (): AwaitableWebpage => $this->buildAwaitablePage($options));
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function buildAwaitablePage(array $options): AwaitableWebpage
    {
        $browser = Playwright::browser($this->browserType)->launch();

        $context = $browser->newContext([
            'locale' => 'en-US',
            'timezoneId' => 'UTC',
            'colorScheme' => Playwright::defaultColorScheme()->value,
            ...$this->device->context(),
            ...$options,
        ]);

        $context->addInitScript(InitScript::get());

        $url = ComputeUrl::from($this->url);

        $awaitableWebPage = new AwaitableWebpage(
            $context->newPage(),
            $url,
        );

        foreach ($this->routes as $pattern => $handler) {
            $awaitableWebPage->route($pattern, $handler);
        }

        $awaitableWebPage->page()->goto($url, $options);

        return $awaitableWebPage;
    }

    /**
     * @param  array<string, mixed>  &$options
     */
    private function extractHost(array &$options): ?string
    {
        if (! array_key_exists('host', $options)) {
            return null;
        }

        $host = $options['host'];

        unset($options['host']);

        return is_string($host) ? $host : null;
    }

    /**
     * @param  callable(): AwaitableWebpage  $callback
     */
    private function withTemporaryHost(?string $host, callable $callback): AwaitableWebpage
    {
        if ($host === null) {
            return $callback();
        }

        $previousHost = Playwright::host();
        Playwright::setHost($host);

        try {
            return $callback();
        } finally {
            Playwright::setHost($previousHost);
        }
    }
}
