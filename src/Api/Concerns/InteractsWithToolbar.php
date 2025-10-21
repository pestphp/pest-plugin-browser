<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;
use Pest\Browser\Support\ComputeUrl;

/**
 * @mixin Webpage
 */
trait InteractsWithToolbar
{
    /**
     * Reloads the current page.
     */
    public function refresh(): self
    {
        $this->page->reload();

        return $this;
    }

    /**
     * Navigates to the given URL.
     *
     * @param  array<string, mixed>  $options
     */
    public function navigate(string $url, array $options = []): self
    {
        $url = ComputeUrl::from($url);

        $this->page->goto($url, $options);

        return $this;
    }

    /**
     * Navigates to the next page in the history.
     */
    public function forward(): self
    {
        $this->page->forward();

        return $this;
    }

    /**
     * Navigates to the previous page in the history.
     */
    public function back(): self
    {
        $this->page->back();

        return $this;
    }
}
