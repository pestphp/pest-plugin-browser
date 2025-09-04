<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;

/**
 * @mixin Webpage
 */
trait HasWaitCapabilities
{
    /**
     * Waits for the specified load state.
     */
    public function waitForLoadState(string $state = 'load'): self
    {
        $this->page->waitForLoadState($state);

        return $this;
    }

    /**
     * Waits for a JavaScript function to return true.
     *
     * @param  mixed  $arg  Optional argument to pass to the function
     */
    public function waitForFunction(string $content, mixed $arg = null): self
    {
        $this->page->waitForFunction($content, $arg);

        return $this;
    }

    /**
     * Waits for navigation to the specified URL.
     */
    public function waitForURL(string $url): self
    {
        $this->page->waitForURL($url);

        return $this;
    }

    /**
     * Waits for the selector to satisfy state option.
     *
     * @param  array<string, mixed>|null  $options  Additional options like state, strict, timeout
     */
    public function waitForSelector(string $selector, ?array $options = null): self
    {
        $this->page->waitForSelector($selector, $options);

        return $this;
    }
}
