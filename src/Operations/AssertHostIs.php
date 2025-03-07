<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertHostIs
{
    /**
     * @var Page.
     */
    private Page $page;

    /**
     * Asserts that the host of the current page matches the given expected host.
     */
    public function assertHostIs(string $expected): self
    {
        $url = $this->page->url();
        $host = parse_url((string) $url, PHP_URL_HOST);

        expect($host)->toBe($expected);

        return $this;
    }
}
