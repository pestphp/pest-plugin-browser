<?php

declare(strict_types=1);

namespace Pest\Browser;

use Illuminate\Routing\UrlGenerator;
use Pest\Browser\Api\AwaitableWebpage;
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

        $http->start();

        $url = $http->url();

        config(['app.url' => $url]);

        config(['cors.paths' => ['*']]);

        if (app()->bound('url')) {
            $urlGenerator = app('url');

            assert($urlGenerator instanceof UrlGenerator);

            $http->setOriginalAssetUrl($urlGenerator->asset(''));

            $urlGenerator->useOrigin($url);
            $urlGenerator->useAssetOrigin($url);
            $urlGenerator->forceScheme('http');
        }
    }

    /**
     * Browse to the given URL.
     *
     * @param  array<string, mixed>  $options
     */
    public function visit(?string $url = null, array $options = []): AwaitableWebpage
    {
        $browser = Playwright::chromium()->launch();

        $page = $browser->newContext($options)->newPage();

        if ($url !== null) {
            $page->goto($url, $options);
        }

        return new AwaitableWebpage($page);
    }
}
