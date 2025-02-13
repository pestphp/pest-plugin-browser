<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class ElementScreenshot implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector,
        private string $path,
    ) {
        //
    }

    public function compile(): string
    {
        return sprintf("await page.locator('%s').screenshot({ path: '%s' });", $this->selector, $this->path);
    }
}
