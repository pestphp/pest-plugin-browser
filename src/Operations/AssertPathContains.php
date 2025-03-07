<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertPathContains
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the current page URL contains the given path segment.
     */
    public function assertPathContains(string $expected): self
    {
        $url = $this->page->url();
        $path = parse_url((string) $url, PHP_URL_PATH);

        expect($path)->toContain($expected);

        return $this;
    }
}
