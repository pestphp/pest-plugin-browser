<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;
use Pest\Browser\Support\AccessibilityFormatter;
use Pest\Matchers\Any;

/**
 * @mixin Webpage
 *
 * @phpstan-import-type Violations from AccessibilityFormatter
 */
trait MakesCookieAssertions
{
    /**
     * Asserts the page has a cookie.
     *
     * @param  string  $key  The name of the cookie.
     * @param  mixed  $value  The value of the cookie.
     */
    public function assertHasCookie(string $key, mixed $value = new Any()): Webpage
    {
        $cookies = $this->page->cookies();

        expect($cookies)
            ->toHaveKey($key, $value, sprintf(
                'Expected cookie [%s] to be present on the page initially with the url [%s], but it was not found',
                $key,
                $this->initialUrl,
            ));

        return $this;
    }

    /**
     * Asserts the page does not have a specific cookie.
     *
     * @param  string  $key  The name of the cookie.
     * @param  mixed  $value  The value of the cookie.
     */
    public function assertCookieMissing(string $key, mixed $value = new Any()): Webpage
    {
        $cookies = $this->page->cookies();

        expect($cookies)
            ->not()->toHaveKey($key, $value, sprintf(
                'Expected cookie [%s] to not be present on the page initially with the url [%s], but it was found',
                $key,
                $this->initialUrl,
            ));

        return $this;
    }

    /**
     * Asserts there are no cookies on the page.
     */
    public function assertNoCookies(): Webpage
    {
        $cookies = $this->page->cookies();

        expect($cookies)->toBeEmpty(sprintf(
            'Expected no cookies on the page initially with the url [%s], but found %s: %s',
            $this->initialUrl,
            count($cookies),
            implode(', ', array_keys($cookies)),
        ));

        return $this;
    }
}
