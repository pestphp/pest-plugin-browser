<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class Refresh extends Operation
{
    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return 'await page.reload();';
    }
}
