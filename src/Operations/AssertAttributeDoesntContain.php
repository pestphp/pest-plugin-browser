<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertAttributeDoesntContain
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that the attribute value does not contain the specified substring.
     */
    public function assertAttributeDoesntContain(string $selector, string $attribute, string $substring): self
    {
        $value = $this->page->getAttribute($selector, $attribute);

        expect($value)->toBeString()
            ->and($value)->not()->toContain($substring);

        return $this;
    }
}
