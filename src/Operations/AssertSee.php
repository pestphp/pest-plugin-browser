<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

final readonly class AssertSee extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $text,
        private bool $ignoreCase = false,
    ) {
        parent::__construct();
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
