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
    public function waitForEvent(string $state): self
    {
        $this->page->waitForLoadState($state);

        return $this;
    }
}
