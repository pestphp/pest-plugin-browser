<?php

namespace Pest\Browser\Locators;

use Pest\Browser\Contracts\Locator;
use Pest\Browser\Traits\StrOrRegExp;

final readonly class GetByRole implements Locator
{
    use StrOrRegExp;

    public function __construct(
        private string $role,
        private string $name
    )
    {
        //
    }

    public function compile(): string
    {
        return sprintf(
            "page.getByRole('%s', { name: %s })",
            $this->role,
            $this->strOrRegExp($this->name)
        );
    }
}
