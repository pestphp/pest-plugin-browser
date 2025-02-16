<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertQueryStringHas implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $name,
        private ?string $value = null,
    ) {}

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        if ((bool)$this->value) {
            return sprintf(
                "const url = new URL(page.url());\nawait expect(url.searchParams.get('%s')).toBe('%s')",
                $this->name,
                $this->value
            );
        }

        return sprintf("const url = new URL(page.url());\n await expect(url.searchParams.has('%s')).toBeTruthy()", $this->name);

    }
}
