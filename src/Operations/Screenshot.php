<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait Screenshot
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Make screenshot of the page.
     */
    public function screenshot(?string $filename = null): self
    {
        $this->page->screenshot($filename);

        return $this;
    }
}
