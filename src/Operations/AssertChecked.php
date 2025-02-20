<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertChecked implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $element,
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf('await expect(page.locator(\'%s\')).toBeChecked();', $this->element);
    }
}
