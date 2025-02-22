<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;

/**
 * @internal
 */
final readonly class ClickLink extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $text,
        private string $element = 'a',
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return "await page.locator('{$this->element}').filter({ hasText: /{$this->text}/i }).click();";
    }
}
