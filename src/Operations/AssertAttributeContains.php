<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertAttributeContains implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $selector,
        private string $attribute,
        private string $value
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf(
            'const attributeValue = await page.locator("%s").getAttribute("%s"); await expect(attributeValue).toContain("%s");',
            $this->selector,
            $this->attribute,
            $this->value
        );
    }
}
