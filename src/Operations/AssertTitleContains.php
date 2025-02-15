<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

final readonly class AssertTitleContains implements Operation
{
    public function __construct(
        private string $title,
    ) {
        //
    }

    public function compile(): string
    {
        return sprintf('await expect(await page.title()).toMatch(/%s/);', $this->title);
    }
}
