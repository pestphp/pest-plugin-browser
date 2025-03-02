<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertQueryStringMissing
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the query string does not contain the specified parameter.
     */
    public function assertQueryStringMissing(string $expectedParam, ?string $expectedValue = null): self
    {
        $url = $this->page->url();
        parse_str(parse_url((string) $url, PHP_URL_QUERY), $query);

        if ($expectedValue) {
            expect($query[$expectedParam] ?? null)->not()->toBe($expectedValue);
        } else {
            expect($query)->not()->toHaveKey($expectedParam);
        }

        return $this;
    }
}
