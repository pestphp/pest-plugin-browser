<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertQueryStringMissing extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $name,
        private ?string $value = null,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        if ((bool) $this->value) {
            return sprintf(
                "await expect(new URL(await page.url()).searchParams.get('%s')).not.toBe('%s')",
                $this->name,
                $this->value
            );
        }

        return sprintf("await expect(new URL(await page.url()).searchParams.has('%s')).toBeFalsy()", $this->name);
    }
}
