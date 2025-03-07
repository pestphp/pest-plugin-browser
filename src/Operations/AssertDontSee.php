<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Page;

trait AssertDontSee
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Assert that the page does not contain the given text.
     */
    public function assertDontSee(string $expected): self
    {
        $escaped = str_replace('"', '\"', $expected);

        $element = $this->page->querySelector("internal:text=\"{$escaped}\"i");

        if ($element instanceof Element) {
            expect($element->isVisible())->toBeFalse();
        } else {
            expect($element)->toBeNull();
        }

        return $this;
    }
}
