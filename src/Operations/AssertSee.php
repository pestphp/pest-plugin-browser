<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertSee implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $text,
        private bool $ignoreCase = false,
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $text = json_encode($this->text);
        $ignoreCase = json_encode($this->ignoreCase);

        return "await expect(page.locator('body')).toContainText({$text}, { ignoreCase: $ignoreCase });";
    }
}
