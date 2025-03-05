<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Page;

trait AssertVisible
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that an element is visible on the page.
     */
    public function assertVisible(string $selector): self
    {
        $element = $this->page->querySelector($selector);

        expect($element)->toBeInstanceOf(Element::class)
            ->and($element->isVisible())->toBeTrue();

        return $this;
    }
}
