<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Locator;
use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class Click implements Operation
{
    /**
     * Creates an operation instance.
     * @param Locator $locator
     */
    public function __construct(private Locator $locator)
    {
        //
    }

    public function compile(): string
    {
        return sprintf("await %s.click();", $this->locator->compile());
    }
}
