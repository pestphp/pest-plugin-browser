<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\ColorScheme;

/**
 * @internal
 */
final class Playwright
{
    /**
     * Browser types
     *
     * @var array<string, BrowserFactory>
     */
    private static array $browserTypes = [];

    /**
     * Whether to run browsers in headless mode.
     */
    private static bool $headless = true;

    /**
     * Whether to debug assertions.
     */
    private static bool $shouldDebugAssertions = false;

    /**
     * Whether to show the diff on screenshot assertions.
     */
    private static bool $shouldDiffOnScreenshotAssertions = false;

    /**
     * The default browser type.
     */
    private static BrowserType $defaultBrowserType = BrowserType::CHROME;

    /**
     * The default color scheme.
     */
    private static ColorScheme $defaultColorScheme = ColorScheme::LIGHT;

    /**
     * The timeout in milliseconds.
     */
    private static int $timeout = 5_000;

    /**
     * The default userAgent.
     */
    private static ?string $userAgent = null;

    /**
     * The default host.
     */
    private static ?string $host = null;

    /**
     * Get a browser factory for the given browser type.
     */
    public static function browser(BrowserType $browserType): BrowserFactory
    {
        $name = $browserType->toPlaywrightName();

        return self::$browserTypes[$name] ?? self::initialize($name);
    }

    /**
     * Close all browser pages
     */
    public static function close(): void
    {
        foreach (self::$browserTypes as $browserType) {
            $browserType->close();
        }

        self::$browserTypes = [];
    }

    /**
     * Set playwright in non-headless mode.
     */
    public static function headed(): void
    {
        self::$headless = false;
    }

    /**
     * Checks if Playwright is running in headless mode.
     */
    public static function isHeadless(): bool
    {
        return self::$headless;
    }

    /**
     * Set whether to show the diff on screenshot assertions.
     */
    public static function setShouldDiffOnScreenshotAssertions(): void
    {
        self::$shouldDiffOnScreenshotAssertions = true;
    }

    /**
     * Set the default color scheme.
     */
    public static function setColorScheme(ColorScheme $colorScheme): void
    {
        self::$defaultColorScheme = $colorScheme;
    }

    /**
     * Set the timeout for assertions.
     */
    public static function setTimeout(int $timeout): void
    {
        self::$timeout = $timeout;

        Client::instance()->setTimeout($timeout);
    }

    /**
     * Get the timeout for assertions.
     */
    public static function timeout(): int
    {
        return self::$timeout;
    }

    /**
     * Set the default userAgent.
     */
    public static function setUserAgent(string $userAgent): void
    {
        self::$userAgent = $userAgent;
    }

    /**
     * Set the default host.
     */
    public static function setHost(?string $host): void
    {
        self::$host = $host;
    }

    /**
     * Get the default host.
     */
    public static function host(): ?string
    {
        return self::$host;
    }

    /**
     * Get the default color scheme.
     */
    public static function defaultColorScheme(): ColorScheme
    {
        return self::$defaultColorScheme;
    }

    /**
     * Whether to show the diff on screenshot assertions.
     */
    public static function shouldShowDiffOnScreenshotAssertions(): bool
    {
        return self::$shouldDiffOnScreenshotAssertions;
    }

    /**
     * Set whether to debug assertions.
     */
    public static function setShouldDebugAssertions(): void
    {
        self::$shouldDebugAssertions = true;
    }

    /**
     * Whether to debug assertions.
     */
    public static function shouldDebugAssertions(): bool
    {
        return self::$shouldDebugAssertions;
    }

    /**
     * Reset playwright state, reset browser types, without closing them.
     */
    public static function reset(): void
    {
        foreach (self::$browserTypes as $browserType) {
            $browserType->reset();
        }
    }

    /**
     * Sets the default browser type.
     */
    public static function setDefaultBrowserType(BrowserType $browserType): void
    {
        self::$defaultBrowserType = $browserType;
    }

    /**
     * Get the default browser type.
     */
    public static function defaultBrowserType(): BrowserType
    {
        return self::$defaultBrowserType;
    }

    /**
     * Executes a callback with a temporary timeout.
     *
     * @template TReturnType
     *
     * @param  callable(): TReturnType  $callback
     * @return TReturnType
     */
    public static function usingTimeout(int $timeout, callable $callback): mixed
    {
        $previousTimeout = Client::instance()->timeout();
        Client::instance()->setTimeout($timeout);

        try {
            return $callback();
        } finally {
            Client::instance()->setTimeout($previousTimeout);
        }
    }

    /**
     * Initialize Playwright
     */
    private static function initialize(string $browser): BrowserFactory
    {
        $response = Client::instance()->execute(
            '',
            'initialize',
            [
                'sdkLanguage' => 'javascript',
            ]
        );

        /** @var array{method: string|null, params: array{type: string|null, guid: string, initializer: array{name: string|null, preLaunchedBrowser?: array{guid: string}}}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method'])
                && $message['method'] === '__create__'
                && isset($message['params']['type'])
                && $message['params']['type'] === 'Playwright'
                && isset($message['params']['initializer']['preLaunchedBrowser'])
            ) {
                self::$browserTypes[$browser]->prelaunch(
                    $message['params']['initializer']['preLaunchedBrowser']['guid'],
                );
            }

            if (
                isset($message['method'])
                && $message['method'] === '__create__'
                && isset($message['params']['type'])
                && $message['params']['type'] === 'BrowserType'
            ) {
                $name = $message['params']['initializer']['name'] ?? '';

                self::$browserTypes[$name] = new BrowserFactory(
                    $message['params']['guid'],
                    $name,
                    self::$headless,
                    self::$userAgent
                );
            }
        }

        return self::$browserTypes[$browser];
    }
}
