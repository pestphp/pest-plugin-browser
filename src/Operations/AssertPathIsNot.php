<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertPathIsNot extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $path,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return "await expect(new URL(await page.url()).pathname).not.toEqual('{$this->path}')";
    }
}
