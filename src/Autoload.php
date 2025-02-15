<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Plugin;

Plugin::uses(Browser::class);

if (! function_exists('\Pest\Browser\visit')) {
    /**
     * Visits the given URL, and starts a new browser test.
     */
    function visit(string $url): PendingTest
    {
        return (new PendingTest)->visit($url);
    }
}
