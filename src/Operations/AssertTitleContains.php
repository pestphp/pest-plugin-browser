<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertTitleContains extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $title,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return sprintf('await expect(await page.title()).toMatch(/%s/);', $this->title);
    }
}
