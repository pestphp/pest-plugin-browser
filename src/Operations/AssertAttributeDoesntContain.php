<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertAttributeDoesntContain extends Operation
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
            'const attributeValue = await page.locator("%s").getAttribute("%s"); expect(attributeValue.includes("%s")).toBe(false);',
            $this->selector,
            $this->attribute,
            $this->value
        );
    }
}
