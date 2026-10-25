<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\ColorScheme;
use Pest\Browser\Playwright\Playwright;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final readonly class Configuration
{
    /**
     * Defaults the browser to Chrome.
     */
    public function inChrome(): self
    {
        Playwright::setDefaultBrowserType(BrowserType::CHROME);

        return $this;
    }

    /**
     * Defaults the browser to Firefox.
     */
    public function inFirefox(): self
    {
        Playwright::setDefaultBrowserType(BrowserType::FIREFOX);

        return $this;
    }

    /**
     * Defaults the browser to Safari.
     */
    public function inSafari(): self
    {
        Playwright::setDefaultBrowserType(BrowserType::SAFARI);

        return $this;
    }

    /**
     * Sets the theme to light mode.
     */
    public function inLightMode(): self
    {
        Playwright::setColorScheme(ColorScheme::LIGHT);

        return $this;
    }

    /**
     * Sets the theme to dark mode.
     */
    public function inDarkMode(): self
    {
        Playwright::setColorScheme(ColorScheme::DARK);

        return $this;
    }

    /**
     * Sets the assertion's timeout in milliseconds.
     */
    public function timeout(int $milliseconds): self
    {
        Playwright::setTimeout($milliseconds);

        return $this;
    }

    /**
     * Uses playwright in headed mode.
     */
    public function headed(): self
    {
        Playwright::headed();

        return $this;
    }

    /**
     * Sets the browsers userAgent.
     */
    public function userAgent(string $userAgent): self
    {
        Playwright::setUserAgent($userAgent);

        return $this;
    }

    /**
     * Sets the host for the server.
     */
    public function withHost(?string $host): self
    {
        Playwright::setHost($host);

        return $this;
    }

    /**
     * Enables debug mode for assertions.
     */
    public function debug(): self
    {
        Playwright::setShouldDebugAssertions();

        return $this;
    }

    /**
     * Enables diff mode for screenshot assertions.
     */
    public function diff(): self
    {
        Playwright::setShouldDiffOnScreenshotAssertions();

        return $this;
    }
}
