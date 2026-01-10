<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Exceptions\BrowserExpectationFailedException;
use Pest\Browser\Execution;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Page;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @internal
 */
final class DownloadCollector
{
    /**
     * The collected downloads.
     *
     * @var array<int, PendingDownload>
     */
    private array $downloads = [];

    public function __construct(
        private readonly Page $page,
        private readonly string $pageGuid,
    ) {
        Client::instance()->startCollectingDownloads($this->pageGuid, $this);
    }

    /**
     * Adds a download to the collection.
     *
     * @internal This method is called by the Client when a download event is received.
     */
    public function add(string $url, string $suggestedFilename, string $artifactGuid): void
    {
        $download = new PendingDownload($this->page);
        $download->resolve($url, $suggestedFilename, $artifactGuid);
        $this->downloads[] = $download;
    }

    /**
     * Returns the collected downloads, optionally waiting for an expected count.
     *
     * @return array<int, PendingDownload>
     */
    public function all(?int $count = null): array
    {
        if ($count !== null) {
            $this->waitForCount($count);
        }

        return $this->downloads;
    }

    /**
     * Stops collecting and cleans up.
     */
    public function stop(): void
    {
        Client::instance()->stopCollectingDownloads($this->pageGuid);
    }

    /**
     * Waits until the expected number of downloads have been collected.
     */
    private function waitForCount(int $count): void
    {
        Execution::instance()->waitForExpectation(function () use ($count): void {
            if (count($this->downloads) < $count) {
                throw BrowserExpectationFailedException::from(
                    $this->page,
                    new ExpectationFailedException(
                        sprintf('Expected %d downloads, but only %d received', $count, count($this->downloads))
                    ),
                );
            }
        });

        if (count($this->downloads) > $count) {
            throw BrowserExpectationFailedException::from(
                $this->page,
                new ExpectationFailedException(
                    sprintf('Expected %d downloads, but %d received', $count, count($this->downloads))
                ),
            );
        }
    }
}
