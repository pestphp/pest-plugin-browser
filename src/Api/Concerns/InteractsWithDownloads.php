<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Illuminate\Support\Collection;
use Pest\Browser\Api\Download;

trait InteractsWithDownloads
{
    /**
     * Executes a callback and captures the download it triggers.
     *
     * @param  callable(self): void  $callback
     */
    public function expectDownload(callable $callback): Download
    {
        $download = $this->page->pendingDownload();

        $callback($this);

        return $download;
    }

    /**
     * Executes a callback and captures all downloads it triggers.
     *
     * @param  callable(self): void  $callback
     * @return Collection<int, Download>
     */
    public function expectDownloads(callable $callback, ?int $count = null): Collection
    {
        $collector = $this->page->downloadCollector();

        try {
            $callback($this);

            return collect($collector->all($count));
        } finally {
            $collector->stop();
        }
    }
}
