<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Exception;

/**
 * @internal
 */
final class Context
{
    use Concerns\InteractsWithPlaywright;

    /**
     * Indicates whether the browser context is closed.
     */
    private bool $closed = false;

    /**
     * Creates a new context instance.
     */
    public function __construct(
        private readonly Browser $browser,
        private readonly string $guid
    ) {
        //
    }

    /**
     * Gets the browser instance.
     */
    public function browser(): Browser
    {
        return $this->browser;
    }

    /**
     * Creates a new page in the context.
     */
    public function newPage(): Page
    {
        $response = Client::instance()->execute($this->guid, 'newPage');

        $frameGuid = '';
        $pageGuid = '';
        $artifactGuid = null;

        /** @var array{method: string|null, params: array{type: string|null, guid: string, initializer: array{url: string, mainFrame?: array{guid: string}, video?: array{guid: string}}}, result: array{page: array{guid: string|null}}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === '__create__' && (isset($message['params']['type']) && $message['params']['type'] === 'Frame')) {
                $frameGuid = $message['params']['guid'];
            }

            if (isset($message['result']['page']['guid'])) {
                $pageGuid = $message['result']['page']['guid'];
            }

            // The video recording artifact GUID is sent in the Page __create__ initializer
            if (
                isset($message['method'], $message['params']['type'], $message['params']['initializer']['video']['guid'])
                && $message['method'] === '__create__'
                && $message['params']['type'] === 'Page'
            ) {
                $artifactGuid = $message['params']['initializer']['video']['guid'];
            }
        }

        return new Page($this, $pageGuid, $frameGuid, $artifactGuid);
    }

    /**
     * Closes the browser context.
     */
    public function close(): void
    {
        if ($this->browser->isClosed() || $this->closed) {
            return;
        }

        try {
            // fix this...
            $response = $this->sendMessage('close');
            $this->processVoidResponse($response);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'has been closed')) {
                return;
            }

            throw $e;
        }

        $this->closed = true;
    }

    /**
     * Checks if the browser context is closed.
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Adds a script which will be evaluated.
     */
    public function addInitScript(string $script): self
    {
        $response = $this->sendMessage('addInitScript', ['source' => $script]);
        $this->processVoidResponse($response);

        return $this;
    }
}
