<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class Pause extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private int $milliseconds
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf('await page.waitForTimeout(%d);', $this->milliseconds);
    }
}
