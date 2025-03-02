<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait Refresh
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Refresh the page.
     */
    public function refresh(): self
    {
        $this->page->reload();

        return $this;
    }
}
