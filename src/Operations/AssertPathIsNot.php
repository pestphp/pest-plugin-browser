<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertPathIsNot
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the current page path does not match the given path.
     */
    public function assertPathIsNot(string $expected): self
    {
        $url = $this->page->url();
        $path = parse_url((string) $url, PHP_URL_PATH);

        expect($path)->not()->toBe($expected);

        return $this;
    }
}
