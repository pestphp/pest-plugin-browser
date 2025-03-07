<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;
use Pest\Browser\Playwright\Playwright;

trait Visit
{
    /**
     * Page
     */
    private Page $page;

    /**
     * Visits a given URL
     */
    public function visit(string $url): self
    {
        $browser = Playwright::chromium()->launch();
        $this->page = $browser->newPage();
        $this->page->goto($url);

        return $this;
    }
}
