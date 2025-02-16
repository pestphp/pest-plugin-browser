<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class RightClick implements Operation
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
        return "await page.locator('{$this->selector}').click({ button: 'right' });";
    }
}
