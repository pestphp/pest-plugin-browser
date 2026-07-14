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
    private const string COOKIES_KEY = '__pestCookies';

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
     * Sets the color scheme to dark mode.
     */
    public function inDarkMode(): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'colorScheme' => ColorScheme::DARK->value,
            ...$this->options,
        ]);
    }

    /**
     * Sets the color scheme to light mode.
     */
    public function inLightMode(): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'colorScheme' => ColorScheme::LIGHT->value,
            ...$this->options,
        ]);
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
        ]);
    }

    /**
     * Sets the userAgent for the page.
     */
    public function withUserAgent(string $userAgent): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'userAgent' => $userAgent,
            ...$this->options,
        ]);
    }

    /**
     * Sets the host for the server.
     */
    public function withHost(string $host): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'host' => $host,
            ...$this->options,
        ]);
    }

    /**
     * Sets the timezone for the page.
     */
    public function withTimezone(string $timezone): self
    {
        return new self($this->browserType, $this->device, $this->url, [
            'timezoneId' => $timezone,
            ...$this->options,
        ]);
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
        ]);
    }

    /**
     * Sets a cookie for the browser context.
     *
     * @param  array<string, mixed>  $options
     */
    public function withCookie(string $name, string $value, array $options = []): self
    {
        return $this->withCookies([['name' => $name, 'value' => $value] + $options]);
    }

    /**
     * Sets multiple cookies for the browser context.
     *
     * @param  array<int, array<string, mixed>>  $cookies
     */
    public function withCookies(array $cookies): self
    {
        /** @var array<int, array<string, mixed>> $existing */
        $existing = $this->options[self::COOKIES_KEY] ?? [];

        return new self($this->browserType, $this->device, $this->url, [
            ...$this->options,
            self::COOKIES_KEY => [...$existing, ...$cookies],
        ]);
    }

    /**
     * Defaults each cookie's url to the navigation target when neither
     * url nor domain is given — Playwright requires one of them.
     *
     * @param  array<int, array<string, mixed>>  $cookies
     * @return array<int, array<string, mixed>>
     */
    private static function resolveCookieUrls(array $cookies, string $targetUrl): array
    {
        return array_map(
            static function (array $cookie) use ($targetUrl): array {
                if (! isset($cookie['url']) && ! isset($cookie['domain'])) {
                    $cookie['url'] = $targetUrl;
                }

                return $cookie;
            },
            $cookies,
        );
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
        /** @var array<int, array<string, mixed>> $cookies */
        $cookies = $options[self::COOKIES_KEY] ?? [];
        unset($options[self::COOKIES_KEY]);

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

        if ($cookies !== []) {
            $context->addCookies(self::resolveCookieUrls($cookies, $url));
        }

        return new AwaitableWebpage(
            $context->newPage()->goto($url, $options),
            $url,
        );
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
