<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Playwright\Playwright;
use Pest\Plugin;

Plugin::uses(Browser::class);

if (! function_exists('\Pest\Browser\page')) {
    /**
     * Visits the given URL, and starts a new browser test.
     */
    function page(?string $url = null): Page
    {
        ServerManager::instance()->http()->start();

        Client::instance()->connectTo(
            ServerManager::instance()->playwright()->url().'?browser=chromium',
        );

        $browser = Playwright::chromium()->launch();
        $page = $browser->newPage();

        if ($url !== null) {
            $page->goto($url);
        }

        return $page;
    }
}
