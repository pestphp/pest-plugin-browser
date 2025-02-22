<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class UnCheck extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $element,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return "await page.locator('{$this->element}').setChecked(false);";
    }
}
