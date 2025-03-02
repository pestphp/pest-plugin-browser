<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait Click
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Clicks an element by its selector.
     */
    public function click(string $selector): self
    {
        $this->page->click($selector);

        return $this;
    }
}
