<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertAttributeContains
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that the attribute value contains the specified substring.
     */
    public function assertAttributeContains(string $selector, string $attribute, string $substring): self
    {
        $value = $this->page->getAttribute($selector, $attribute);

        expect($value)->toBeString()
            ->and($value)->toContain($substring);

        return $this;
    }
}
