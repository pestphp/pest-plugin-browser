<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Page;

trait AssertNotPresent
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that an element is not present on the page.
     */
    public function assertNotPresent(string $selector): self
    {
        $element = $this->page->querySelector($selector);

        expect($element)->toBeNull();

        return $this;
    }
}
