<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait Uncheck
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Uncheck the checkbox with the given selector.
     */
    public function uncheck(string $selector): self
    {
        $this->page->querySelector($selector)->uncheck();

        return $this;
    }
}
