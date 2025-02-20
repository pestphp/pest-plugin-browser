<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertPathIs implements Operation
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
        $pattern = str_replace('\*', '.*', preg_quote($this->path, '/'));

        return "await expect(new URL(await page.url()).pathname).toMatch(/^$pattern$/u)";
    }
}
