<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\Frontend\Frontend;
use PHPUnit\Framework\Assert;

if (! function_exists('Pest\Browser\frontend')) {
    /**
     * Returns the URL of the given path on the configured frontend, for the HTTP server of this test process.
     *
     * Skips the test when the frontend cannot start.
     */
    function frontend(string $path = '/', ?string $subdomain = null): string
    {
        try {
            return Frontend::instance()->url($path, $subdomain);
        } catch (FrontendUnavailableException $exception) {
            Assert::markTestSkipped($exception->getMessage());
        }
    }
}
