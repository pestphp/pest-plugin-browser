<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Page;

trait AssertNotChecked
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that an element is not checked.
     */
    public function assertNotChecked(string $selector): self
    {
        $element = $this->page->querySelector($selector);

        expect($element)->toBeInstanceOf(Element::class)
            ->and($element->isChecked())->toBeFalse();

        return $this;
    }
}
