<?php

declare(strict_types=1);

namespace Pest\Browser\Contracts;

use Pest\Browser\Exceptions\BrowserOperationException;
use Pest\Browser\ValueObjects\TestResult;

/**
 * @internal
 */
interface Operation
{
    /**
     * Compile the operation to JavaScript code.
     */
    public function compile(): string;

    /**
     * Fail the operation and throw an exception.
     *
     * @throws BrowserOperationException
     */
    public function fail(TestResult $testResult): void;
}
