<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\PendingAwaitablePopup;

trait InteractsWithPopups
{
    /**
     * Set up a popup handler for this page.
     */
    public function pendingPopup(): PendingAwaitablePopup
    {
        return $this->page->pendingPopup();
    }

    /**
     * Remove any previously set popup handler.
     */
    public function removePendingPopup(): self
    {
        $this->page->removePendingPopup();

        return $this;
    }

    /**
     * Check if a popup handler is currently set.
     */
    public function hasPendingPopup(): bool
    {
        return $this->page->hasPendingPopup();
    }
}
