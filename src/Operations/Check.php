<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class Check implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $element,
    ) {}

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return "await page.locator('{$this->element}').setChecked(true);";
    }
}
