<?php

declare(strict_types=1);

namespace Pest\Browser\Contracts;

use PHPUnit\Framework\ExpectationFailedException;

interface Assertion extends Operation
{
    /**
     * Fails the assertion.
     *
     * @throws ExpectationFailedException
     */
    public function fail(string $browser): void;
}
