<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait Back
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Navigates to the previous page in the history.
     */
    public function back(): self
    {
        $this->page->back();

        return $this;
    }
}
