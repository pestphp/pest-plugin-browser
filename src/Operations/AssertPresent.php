<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Page;

trait AssertPresent
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that an element is present on the page.
     */
    public function assertPresent(string $selector): self
    {
        $element = $this->page->querySelector($selector);

        expect($element)->toBeInstanceOf(Element::class);

        return $this;
    }
}
