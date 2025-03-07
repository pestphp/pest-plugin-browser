<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertTitle
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Assert that the page title matches the expected value.
     */
    public function assertTitle(string $expected): self
    {
        $title = $this->page->title();

        expect($title)->toBe($expected);

        return $this;
    }
}
