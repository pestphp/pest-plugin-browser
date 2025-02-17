<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Assertion;
use Pest\Browser\Support\Str;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @internal
 */
final readonly class AssertTitle implements Assertion
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

    /**
     * {@inheritDoc}
     */
    public function fail(string $browser): void
    {
        throw new ExpectationFailedException(sprintf(
            '[%s] Failed asserting that the title matches "%s"',
            $browser,
            $this->title,
        ));
    }
}
