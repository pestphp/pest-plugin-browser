<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class Clear implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $selector = json_encode($this->selector);

        return "await page.locator({$selector}).clear();";
    }
}
