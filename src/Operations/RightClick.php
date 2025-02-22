<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class RightClick extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $selector = json_encode($this->selector);

        return "await page.locator({$selector}).click({ button: 'right' });";
    }
}
