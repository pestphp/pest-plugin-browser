<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertAttribute extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector,
        private string $attribute,
        private string $value
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf(
            'await expect(page.locator("%s")).toHaveAttribute("%s", "%s");',
            $this->selector,
            $this->attribute,
            $this->value
        );
    }
}
