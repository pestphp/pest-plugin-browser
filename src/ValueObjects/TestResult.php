<?php

declare(strict_types=1);

namespace Pest\Browser\ValueObjects;

/**
 * @internal
 */
final readonly class TestResult
{
    /**
     * The test result.
     */
    public function __construct(
        private bool $ok,
    ) {
        //
    }

    /**
     * Get the test result.
     */
    public function ok(): bool
    {
        return $this->ok;
    }
}
