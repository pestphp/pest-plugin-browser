<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertTitleContains
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the page title contains the given string.
     */
    public function assertTitleContains(string $expected): self
    {
        $title = $this->page->title();

        expect($title)->toContain($expected);

        return $this;
    }
}
