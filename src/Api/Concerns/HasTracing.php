<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;
use Pest\Browser\Playwright\Tracing;

/**
 * @mixin Webpage
 */
trait HasTracing
{
    /**
     * Starts tracing
     */
    public function startTracing(
        bool $screenshots = true,
        bool $snapshots = true,
    ): self {
        $this->page->context()->tracing()->start([
            'screenshots' => $screenshots,
            'snapshots' => $snapshots,
        ]);
        $this->page->context()->tracing()->startChunk();

        return $this;
    }

    /**
     * Stops tracing and saves artifact
     */
    public function stopTracing(?string $filename = null): self
    {
        $artifact = $this->page->context()->tracing()->stopChunk([
            'mode' => 'archive',
        ]);
        $this->page->context()->tracing()->stop();

        $artifact->saveAs(['path' => Tracing::path($filename)]);

        return $this;
    }
}
