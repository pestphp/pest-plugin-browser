<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Page;

trait AssertChecked
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Assert that an element is checked.
     */
    public function assertChecked(string $selector): self
    {
        $element = $this->page->querySelector($selector);

        expect($element)->toBeInstanceOf(Element::class)
            ->and($element->isChecked())->toBeTrue();

        return $this;
    }
}
