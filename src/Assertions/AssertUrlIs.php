<?php

declare(strict_types=1);

namespace Pest\Browser\Assertions;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class AssertUrlIs implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $url,
    ) {
        //
    }

    public function compile(): string
    {
        $pattern = str_replace('\*', '.*', preg_quote($this->url, '/'));

        return sprintf('await expect(page.url()).toMatch(/%s/i);', $pattern);
    }
}
