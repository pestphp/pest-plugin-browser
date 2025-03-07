<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertSchemeIsNot
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the scheme of the current page URL is not the specified one.
     */
    public function assertSchemeIsNot(string $expected): self
    {
        $url = $this->page->url();
        $scheme = parse_url((string) $url, PHP_URL_SCHEME);

        expect($scheme)->not()->toBe($expected);

        return $this;
    }
}
