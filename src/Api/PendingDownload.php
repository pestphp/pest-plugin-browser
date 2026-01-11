<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Generator;
use Pest\Browser\Exceptions\BrowserExpectationFailedException;
use Pest\Browser\Execution;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Page;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @internal
 */
final class PendingDownload
{
    /**
     * The download URL.
     */
    private ?string $url = null;

    /**
     * The suggested filename for the download.
     */
    private ?string $suggestedFilename = null;

    /**
     * The Playwright artifact GUID.
     */
    private ?string $artifactGuid = null;

    /**
     * Creates a new pending download instance.
     */
    public function __construct(
        private readonly Page $page,
    ) {}

    /**
     * Resolves the download with the given details.
     *
     * @internal This method is called by the Client when a download event is received.
     */
    public function resolve(string $url, string $suggestedFilename, string $artifactGuid): void
    {
        $this->url = $url;
        $this->suggestedFilename = $suggestedFilename;
        $this->artifactGuid = $artifactGuid;
    }

    /**
     * Returns the download URL.
     */
    public function url(): string
    {
        $this->ensureResolved();

        return (string) $this->url;
    }

    /**
     * Returns the suggested filename for the download.
     */
    public function suggestedFilename(): string
    {
        $this->ensureResolved();

        return (string) $this->suggestedFilename;
    }

    /**
     * Saves the download to the given path.
     */
    public function saveAs(string $path): self
    {
        $this->ensureResolved();

        iterator_to_array($this->artifact('saveAs', ['path' => $path]));

        return $this;
    }

    /**
     * Returns the path where the download was saved.
     */
    public function path(): string
    {
        return $this->artifactValue('pathAfterFinished') ?? '';
    }

    /**
     * Returns the contents of the downloaded file.
     */
    public function contents(): string
    {
        return (string) file_get_contents($this->path());
    }

    /**
     * Returns the failure message, or null if successful.
     */
    public function failure(): ?string
    {
        return $this->artifactValue('failure');
    }

    /**
     * Checks if the download was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->failure() === null;
    }

    /**
     * Assert the download has the expected filename.
     */
    public function assertFilename(string $expected): self
    {
        expect($this->suggestedFilename())->toBe($expected);

        return $this;
    }

    /**
     * Assert the download filename contains the expected string.
     */
    public function assertFilenameContains(string $expected): self
    {
        expect($this->suggestedFilename())->toContain($expected);

        return $this;
    }

    /**
     * Assert the download URL contains the expected string.
     */
    public function assertUrlContains(string $expected): self
    {
        expect($this->url())->toContain($expected);

        return $this;
    }

    /**
     * Assert the download content contains the expected string.
     */
    public function assertContentContains(string $expected): self
    {
        expect($this->contents())->toContain($expected);

        return $this;
    }

    /**
     * Assert the download was successful.
     */
    public function assertSuccessful(): self
    {
        expect($this->isSuccessful())->toBeTrue();

        return $this;
    }

    /**
     * Assert the download failed.
     */
    public function assertFailed(): self
    {
        expect($this->isSuccessful())->toBeFalse();

        return $this;
    }

    /**
     * Executes a method on the download artifact and extracts the result value.
     */
    private function artifactValue(string $method): ?string
    {
        $this->ensureResolved();

        foreach ($this->artifact($method) as $message) {
            $result = $message['result'] ?? [];
            $value = is_array($result) ? ($result['value'] ?? null) : null;

            if (is_string($value) || $value === null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Executes a method on the download artifact.
     *
     * @param  array<string, mixed>  $params
     * @return Generator<array<string, mixed>>
     */
    private function artifact(string $method, array $params = []): Generator
    {
        assert($this->artifactGuid !== null);

        return Client::instance()->execute($this->artifactGuid, $method, $params);
    }

    /**
     * Ensures the download has been resolved.
     */
    private function ensureResolved(): void
    {
        if ($this->artifactGuid !== null) {
            return;
        }

        Execution::instance()->waitForExpectation(function (): void {
            if ($this->artifactGuid === null) {
                throw BrowserExpectationFailedException::from(
                    $this->page,
                    new ExpectationFailedException('No download started'),
                );
            }
        });
    }
}
