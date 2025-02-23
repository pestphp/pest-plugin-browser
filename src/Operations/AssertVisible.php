<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertVisible extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $escapedSelector = str_replace("'", "\'", $this->selector);

        return "await expect(page.locator('{$escapedSelector}')).toBeVisible();";
    }
}
