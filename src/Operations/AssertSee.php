<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Page;

trait AssertSee
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the page contains the given text.
     */
    public function assertSee(string $expected): self
    {
        $escaped = str_replace('"', '\"', $expected);

        $element = $this->page->querySelector("internal:text=\"{$escaped}\"i");

        expect($element)->toBeInstanceOf(Element::class);

        $isVisible = $element->isVisible();

        expect($isVisible)->toBeTrue();

        return $this;
    }
}
