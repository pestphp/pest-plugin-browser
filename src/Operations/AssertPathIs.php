<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;
use Pest\Browser\Support\Str;

trait AssertPathIs
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the current page path matches the given expected path.'
     */
    public function assertPathIs(string $expected): self
    {
        $url = $this->page->url();
        $path = parse_url((string) $url, PHP_URL_PATH);

        if (Str::isRegex($expected)) {
            expect((bool) preg_match($expected, $path))->toBeTrue();
        } else {
            expect($path)->toBe($expected);
        }

        return $this;
    }
}
