<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertPathContains implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $path,
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return "await expect(new URL(await page.url()).pathname).toEqual(expect.stringContaining('$this->path'))";
    }
}
