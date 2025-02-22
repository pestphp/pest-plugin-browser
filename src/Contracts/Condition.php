<?php

declare(strict_types=1);

namespace Pest\Browser\Contracts;

/**
 * @internal
 */
interface Condition
{
    /**
     * Compile the condition to JavaScript code.
     */
    public function compile(): string;
}
