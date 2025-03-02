<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertHostIsNot
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the host of the current page is not equal to the given expected host.
     */
    public function assertHostIsNot(string $expected): self
    {
        $url = $this->page->url();
        $host = parse_url((string) $url, PHP_URL_HOST);

        expect($host)->not()->toBe($expected);

        return $this;
    }
}
