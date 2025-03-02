<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait DoubleClick
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Double-clicks an element matching the specified selector.
     */
    public function doubleClick(string $selector): self
    {
        $this->page->doubleClick($selector);

        return $this;
    }
}
