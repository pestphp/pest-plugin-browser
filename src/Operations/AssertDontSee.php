<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertDontSee implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $text,
        private bool $ignoreCase = true,
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $ignoreCase = $this->ignoreCase ? 'i' : '';

        return sprintf('await expect(page.locator(\'body\')).not.toHaveText(/%s/'.$ignoreCase.');', $this->text);
    }
}
