<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait Forward
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Navigates to the next page in the history.
     */
    public function forward(): self
    {
        $this->page->forward();

        return $this;
    }
}
