<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait Check
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Check the checkbox with the given selector.
     */
    public function check(string $selector): self
    {
        $this->page->querySelector($selector)->check();

        return $this;
    }
}
