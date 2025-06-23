<?php

declare(strict_types=1);

namespace Pest\Browser;

use Illuminate\Routing\UrlGenerator;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Page;

/**
 * @internal
 */
trait Browser
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

        ServerManager::instance()->http()->start();

        $url = ServerManager::instance()->http()->url();

        config(['app.url' => $url]);

        if (app()->bound('url')) {
            $urlGenerator = app('url');

            assert($urlGenerator instanceof UrlGenerator);

            $urlGenerator->useOrigin($url);
        }
    }

    /**
     * gets the page instance for given URL.
     *
     * @param  string|null  $url  The URL to visit
     * @param  array<string, mixed>  $options  Options for the page, e.g. ['hasTouch' => true]
     */
    public function page(?string $url = null, array $options = []): Page
    {
        return page($url, $options);
    }
}
