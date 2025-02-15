<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertSee implements Operation
{
    public function __construct(
        private string $text,
        private bool $ignoreCase = false,
    ) {
        //
    }

    public function compile(): string
    {
        $ignoreCase = $this->ignoreCase ? 'i' : '';

        return sprintf('await expect(page.locator(\'body\')).toHaveText(/%s/'.$ignoreCase.');', $this->text);
    }
}
