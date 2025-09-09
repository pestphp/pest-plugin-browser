<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\Device;
use Pest\Browser\Playwright\Playwright;

trait PageConfiguration
{
    /**
     * Get the global element shortcuts for the site.
     *
     * @return array<string, string>
     */
    public static function siteElements(): array
    {
        return [];
    }

    /**
     * Get the timezone for the page.
     */
    public function timezone(): string
    {
        return '';
    }

    /**
     * Get the locale for the page.
     */
    public function locale(): string
    {
        return '';
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [];
    }

    /**
     * Get the device for the page.
     */
    public function device(): Device
    {
        return Device::DESKTOP;
    }

    /**
     * Get the browser type for the page.
     */
    public function browserType(): BrowserType
    {
        return Playwright::defaultBrowserType();
    }
}
