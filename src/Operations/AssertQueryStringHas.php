<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Playwright\Page;

trait AssertQueryStringHas
{
    /**
     * Page.
     */
    private Page $page;

    /**
     * Asserts that the query string contains the specified parameter with the expected value.
     */
    public function assertQueryStringHas(string $expectedParam, ?string $expectedValue = null): self
    {
        $url = $this->page->url();
        parse_str(parse_url((string) $url, PHP_URL_QUERY), $query);

        expect($query)->toHaveKey($expectedParam);

        if ($expectedValue) {
            expect($query[$expectedParam])->toBe($expectedValue);
        }

        return $this;
    }
}
