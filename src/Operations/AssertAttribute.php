<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertAttribute
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that the attribute of an element matches the expected value.
     */
    public function assertAttribute(string $selector, string $attribute, string $expectedValue): self
    {
        $value = $this->page->getAttribute($selector, $attribute);

        expect($value)->toBe($expectedValue);

        return $this;
    }
}
