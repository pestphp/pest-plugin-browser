<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;
use Pest\Browser\Support\Str;

trait AssertUrlIs
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the current page URL matches the given URL.
     */
    public function assertUrlIs(string $expected): self
    {
        if (Str::isRegex($expected)) {
            expect((bool) preg_match($expected, $this->page->url()))->toBeTrue();
        } else {
            expect($this->page->url())->toBe($expected);
        }

        return $this;
    }
}
