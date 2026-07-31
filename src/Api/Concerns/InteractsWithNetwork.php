<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;

/**
 * @mixin Webpage
 */
trait InteractsWithNetwork
{
    /**
     * Disconnects the browser from the network.
     */
    public function offline(): self
    {
        $this->page->context()->setOffline(true);

        return $this;
    }

    /**
     * Reconnects the browser to the network.
     */
    public function online(): self
    {
        $this->page->context()->setOffline(false);

        return $this;
    }
}
