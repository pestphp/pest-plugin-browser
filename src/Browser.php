<?php

declare(strict_types=1);

namespace Pest\Browser;

use PHPUnit\Framework\TestCase;

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
}
