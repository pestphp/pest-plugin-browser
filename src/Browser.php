<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Playwright\Page;

/**
 * @internal
 */
trait Browser
{
    /**
     * Visits the given URL, and starts a new browser test.
     */
    public function visit(string $url): PendingTest
    {
        return visit($url);
    }

    /**
     * gets the page instance for given URL.
     */
    public function page(?string $url = null): Page
    {
        return page($url);
    }
}
