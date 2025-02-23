<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Operation;
use Pest\Browser\Support\Str;

/**
 * @internal
 */
final readonly class AssertUrlIs extends Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $url,
    ) {
        parent::__construct();
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $url = Str::isRegex($this->url) ? $this->url : json_encode($this->url);

        return sprintf('await expect(page).toHaveURL(%s);', $url);
    }
}
