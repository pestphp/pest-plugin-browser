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
        private TestResultResponse $response,
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

    /**
     * Get the final response from the test result.
     */
    public function response(): TestResultResponse
    {
        return $this->response;
    }
}
