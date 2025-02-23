<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class ClickAtPoint extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private int $x = 0,
        private int $y = 0,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return "await page.mouse.click({$this->x}, {$this->y});";
    }
}
