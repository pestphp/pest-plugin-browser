<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;
use Pest\Browser\Traits\StrOrRegExp;
use Pest\Browser\Traits\NotOperationTrait;

/**
 * @internal
 */
final readonly class ToBeVisible implements Operation
{
    use StrOrRegExp;

    /**
     * Creates an operation instance.
     * @param string $element
     * @param string $name
     */
    public function __construct(
        private string $element,
        private string $name
    )
    {
        //
    }

    public function compile(): string
    {
        return sprintf("await expect(page.getByRole('%s', { name: %s })).toBeVisible();",
            $this->element,
            $this->strOrRegExp($this->name)
        );
    }
}
