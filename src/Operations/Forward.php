<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class Forward implements Operation
{
    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return 'await page.goForward();';
    }
}
