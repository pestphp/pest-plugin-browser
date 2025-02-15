<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;
use Pest\Browser\Support\Str;

/**
 * @internal
 */
final readonly class AssertTitle implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $title,
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $title = Str::isRegex($this->title) ? $this->title : json_encode($this->title);

        return sprintf('await expect(page).toHaveTitle(%s);', $title);
    }
}
