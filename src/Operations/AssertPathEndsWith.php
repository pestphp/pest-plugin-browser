<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertPathEndsWith
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the current page path ends with the given string.
     */
    public function assertPathEndsWith(string $expected): self
    {
        $url = $this->page->url();
        $path = parse_url((string) $url, PHP_URL_PATH);

        expect($path)->toEndWith($expected);

        return $this;
    }
}
