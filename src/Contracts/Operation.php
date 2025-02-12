<?php

declare(strict_types=1);

namespace Pest\Browser\Contracts;

/**
 * @internal
 */
interface Operation
{
    /**
     * Compile the operation to JavaScript code.
     */
    public function compile(): string;
}
