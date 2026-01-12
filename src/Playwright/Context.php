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

        /** @var array{method: string|null, params: array{type: string|null, guid: string, initializer: array{url: string}}, result: array{page: array{guid: string|null}}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === '__create__' && (isset($message['params']['type']) && $message['params']['type'] === 'Frame')) {
                $frameGuid = $message['params']['guid'];
            }

            if (isset($message['result']['page']['guid'])) {
                $pageGuid = $message['result']['page']['guid'];
            }
        }

        return new Page($this, $pageGuid, $frameGuid);
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

    /**
     * Gets the storage state (cookies, localStorage, sessionStorage).
     *
     * @return array{cookies: array<array{name: string, value: string, domain: string, path: string, expires: float, httpOnly: bool, secure: bool, sameSite: string}>, origins: array<array{origin: string, localStorage: array<array{name: string, value: string}>}>}
     */
    public function storageState(): array
    {
        $response = $this->sendMessage('storageState');

        /** @var array{result: array{cookies: array, origins: array}} $message */
        foreach ($response as $message) {
            if (isset($message['result'])) {
                return $message['result'];
            }
        }

        return ['cookies' => [], 'origins' => []];
    }

    /**
     * Adds cookies into this browser context.
     *
     * @param  array<array{name: string, value: string, domain?: string, path?: string, expires?: float, httpOnly?: bool, secure?: bool, sameSite?: string}>  $cookies
     */
    public function addCookies(array $cookies): self
    {
        $response = $this->sendMessage('addCookies', ['cookies' => $cookies]);
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Gets all cookies in this browser context.
     *
     * @param  array<string>  $urls  Optional URLs to filter cookies
     * @return array<array{name: string, value: string, domain: string, path: string, expires: float, httpOnly: bool, secure: bool, sameSite: string}>
     */
    public function cookies(array $urls = []): array
    {
        $response = $this->sendMessage('cookies', $urls !== [] ? ['urls' => $urls] : []);

        /** @var array{result: array{cookies: array}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['cookies'])) {
                return $message['result']['cookies'];
            }
        }

        return [];
    }

    /**
     * Clears all cookies from this browser context.
     */
    public function clearCookies(): self
    {
        $response = $this->sendMessage('clearCookies');
        $this->processVoidResponse($response);

        return $this;
    }
}
