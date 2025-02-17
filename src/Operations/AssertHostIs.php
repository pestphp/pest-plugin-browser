<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertHostIs implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $host,
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf("await expect(new URL(await page.url()).host).toBe('%s');", $this->host);
    }
}
