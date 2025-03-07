<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertSchemeIs
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the URL scheme is the expected one.
     */
    public function assertSchemeIs(string $expected): self
    {
        $url = $this->page->url();
        $scheme = parse_url((string) $url, PHP_URL_SCHEME);

        expect($scheme)->toBe($expected);

        return $this;
    }
}
