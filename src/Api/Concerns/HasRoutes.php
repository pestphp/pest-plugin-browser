<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;
use Pest\Browser\Playwright\Route;

/**
 * @mixin Webpage
 */
trait HasRoutes
{
    /**
     * @param  callable(Route): bool  $handler
     */
    public function route(
        string $pattern,
        callable $handler,
    ): void {
        Route::registerRouteHandler($pattern, $handler);
        $this->page->context()->setNetworkInterceptionPatterns([
            'patterns' => [
                [
                    'glob' => $pattern,
                ],
            ],
        ]);
    }
}
