<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertPathBeginsWith
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts the current page path starts with the given path.
     */
    public function assertPathBeginsWith(string $expected): self
    {
        $url = $this->page->url();
        $path = parse_url((string) $url, PHP_URL_PATH);

        expect($path)->toStartWith($expected);

        return $this;
    }
}
