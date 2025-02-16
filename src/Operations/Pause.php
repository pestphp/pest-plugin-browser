<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class Pause implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private int $milliseconds
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf('await (new Promise(resolve => setTimeout(resolve, %d)));', $this->milliseconds);
    }
}
