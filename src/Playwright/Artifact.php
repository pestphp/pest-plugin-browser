<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final readonly class Artifact
{
    use Concerns\InteractsWithPlaywright;

    /**
     * Creates a new context instance.
     */
    public function __construct(
        private string $guid
    ) {
        //
    }

    /**
     * Save artifact
     *
     * @param  array<string, mixed>  $params
     */
    public function saveAs(array $params = []): void
    {
        $response = Client::instance()->execute($this->guid, 'saveAs', $params);
        $this->processVoidResponse($response);
    }
}
