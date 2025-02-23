<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class ClickAndHold extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector,
        private int $duration = 1000,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $selector = json_encode($this->selector);

        return "await page.locator({$selector}).click({ delay: {$this->duration} });";
    }
}
