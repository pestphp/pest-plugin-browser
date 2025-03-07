<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertAttributeMissing
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that the specified attribute is missing from the element matching the given selector.
     */
    public function assertAttributeMissing(string $selector, string $attribute): self
    {
        $value = $this->page->getAttribute($selector, $attribute);

        expect($value)->toBeNull();

        return $this;
    }
}
