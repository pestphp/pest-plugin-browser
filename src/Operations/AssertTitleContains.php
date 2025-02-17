<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Assertion;
use PHPUnit\Framework\ExpectationFailedException;

final readonly class AssertTitleContains implements Assertion
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
        return sprintf('await expect(await page.title()).toMatch(/%s/);', $this->title);
    }

    /**
     * {@inheritDoc}
     */
    public function fail(string $browser): void
    {
        throw new ExpectationFailedException(sprintf(
            '[%s] Failed asserting that the title contains "%s"',
            $browser,
            $this->title,
        ));
    }
}
