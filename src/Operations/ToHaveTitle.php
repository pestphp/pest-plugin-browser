<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class ToHaveTitle implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $title,
    ) {
        //
    }

    public function compile(): string
    {
        return sprintf("await expect(page).toHaveTitle(/%s/);", $this->title);
    }
}
