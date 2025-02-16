<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class Click implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector,
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $escapedSelector = str_replace("'", "\'", $this->selector);

        return "await page.locator('{$escapedSelector}').click();";
    }
}
