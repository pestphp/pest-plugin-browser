<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;
use Pest\Browser\Enums\Device;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Playwright;

/**
 * @internal
 */
trait Browsable
{
    /**
     * Marks the test as a browser test.
     *
     * @internal
     */
    public function __markAsBrowserTest(): void
    {
        Client::instance()->connectTo(
            ServerManager::instance()->playwright()->url(),
        );

        $http = ServerManager::instance()->http();

        $http->bootstrap();
    }

    /**
     * Browse to the given URL.
     *
     * @template TUrl of array<int, string|Page>|string|Page
     *
     * @param  TUrl  $url
     * @param  array<string, mixed>  $options
     * @return (TUrl is array<int, string|Page> ? ArrayablePendingAwaitablePage : PendingAwaitablePage)
     */
    public function visit(array|string|Page $url, array $options = []): ArrayablePendingAwaitablePage|PendingAwaitablePage
    {
        if ($url instanceof Page) {
            $options = [
                ...$this->pageTimezone($url),
                ...$this->pageLocale($url),
                ...$options,
            ];

            return new PendingAwaitablePage(
                $url->browserType(),
                $url->device(),
                $url,
                $options,
            );
        }

        if (is_string($url)) {
            return new PendingAwaitablePage(
                Playwright::defaultBrowserType(),
                Device::DESKTOP,
                $url,
                $options,
            );
        }

        return new ArrayablePendingAwaitablePage(
            array_map(fn (string|Page $singleUrl): PendingAwaitablePage => $this->visit($singleUrl, $options), $url),
        );
    }

    /**
     * Get the locale from page.
     *
     * @return array{locale?: string}
     */
    private function pageLocale(Page $page): array
    {
        $locale = $page->locale();

        if ($locale === '') {
            return [];
        }

        return ['locale' => $locale];
    }

    /**
     * Get the timezone from page.
     *
     * @return array{timezoneId?: string}
     */
    private function pageTimezone(Page $page): array
    {
        $timezone = $page->timezone();

        if ($timezone === '') {
            return [];
        }

        return ['timezoneId' => $timezone];
    }
}
