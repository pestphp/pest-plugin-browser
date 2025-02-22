<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class ClickAtXPath extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $expression,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return "await page.locator('xpath={$this->expression}').click();";
    }
}
