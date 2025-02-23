<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertHostIsNot extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $host,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf("await expect(new URL(await page.url()).host).not.toBe('%s');", $this->host);
    }
}
