<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class Visit extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $url,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf("await page.goto('%s');", $this->url);
    }
}
