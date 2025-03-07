<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait ClickLink
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Clicks a link by its text.
     */
    public function clickLink(string $text): self
    {
        $this->page->click("a>>internal:text=\"{$text}\"i");

        return $this;
    }
}
