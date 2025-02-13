<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class DoesntHaveTitle implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $url,
    ) {
        //
    }

    public function compile(): string
    {
        return (new ToHaveTitle($this->url, not: true))->compile();
    }
}
