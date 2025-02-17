<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Assertion;
use PHPUnit\Framework\ExpectationFailedException;

final readonly class AssertSee implements Assertion
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
        $ignoreCase = $this->ignoreCase ? 'i' : '';

        return sprintf('await expect(page.locator(\'body\')).toHaveText(/%s/'.$ignoreCase.');', $this->text);
    }

    /**
     * {@inheritDoc}
     */
    public function fail(string $browser): void
    {
        throw new ExpectationFailedException(sprintf(
            '[%s] Failed asserting that the page contains the text "%s"',
            $browser,
            $this->text,
        ));
    }
}
