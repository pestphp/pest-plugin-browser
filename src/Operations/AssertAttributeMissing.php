<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertAttributeMissing extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector,
        private string $attribute
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf(
            'const attributeValue = await page.locator("%s").getAttribute("%s"); await expect(attributeValue).toBeNull();',
            $this->selector,
            $this->attribute
        );
    }
}
