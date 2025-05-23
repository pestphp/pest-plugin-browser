<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Playwright\Server;
use Pest\Plugin;
use Pest\Plugins\Parallel;

Plugin::uses(Browser::class);

if (! function_exists('\Pest\Browser\visit')) {
    /**
     * Visits the given URL, and starts a new browser test.
     */
    function visit(string $url): PendingTest
    {
        Server::instance()->start();
        Client::instance()->connectTo(Server::instance()->url('?browser=chromium'));

        return (new PendingTest)->visit($url);
    }
}

if (! function_exists('\Pest\Browser\page')) {
    /**
     * Visits the given URL, and starts a new browser test.
     */
    function page(?string $url = null): Page
    {
        Server::instance()->start();
        Client::instance()->connectTo(Server::instance()->url('?browser=chromium'));

        $browser = Playwright::chromium()->launch();
        $page = $browser->newPage();

        if ($url !== null) {
            $page->goto($url);
        }

        return $page;
    }
}

register_shutdown_function(function (): void {
    if (Parallel::isEnabled() || ! Parallel::isWorker()) {
        Server::instance()->stop();
    }
});
