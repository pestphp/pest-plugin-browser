<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class Click implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $text,
    ) {
        //
    }

    public function compile(): string
    {
        return sprintf("await page.getByText(/%s/).click();", $this->text);
    }
}
