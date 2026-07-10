<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Amp\Websocket\Client\WebsocketConnection;
use Generator;
use Pest\Browser\Exceptions\PlaywrightOutdatedException;
use PHPUnit\Framework\ExpectationFailedException;
use WeakReference;

use function Amp\Websocket\Client\connect;

/**
 * @internal
 */
final class Client
{
    /**
     * Client instance.
     */
    private static ?Client $instance = null;

    /**
     * WebSocket client instance.
     */
    private ?WebsocketConnection $websocketConnection = null;

    /**
     * Registry of Page instances for handling events.
     *
     * @var array<string, WeakReference<Page>>
     */
    private array $pages = [];

    /**
     * Default timeout for requests in milliseconds.
     */
    private int $timeout = 5_000;

    /**
     * Returns the current client instance.
     */
    public static function instance(): self
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Connects to the Playwright server.
     */
    public function connectTo(string $url): void
    {
        if (! $this->websocketConnection instanceof WebsocketConnection) {
            $browser = Playwright::defaultBrowserType()->toPlaywrightName();

            $launchOptions = json_encode([
                'headless' => Playwright::isHeadless(),
                'ignoreHTTPSErrors' => true,
                'bypassCSP' => true,
            ]);

            $this->websocketConnection = connect(
                "ws://$url?browser=$browser&launch-options=$launchOptions",
            );
        }
    }

    /**
     * Executes a method on the Playwright instance.
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $meta
     * @return Generator<array<string, mixed>>
     */
    public function execute(string $guid, string $method, array $params = [], array $meta = []): Generator
    {
        assert($this->websocketConnection instanceof WebsocketConnection, 'WebSocket client is not connected.');

        $requestId = uniqid();

        $requestJson = (string) json_encode([
            'id' => $requestId,
            'guid' => $guid,
            'method' => $method,
            'params' => ['timeout' => $this->timeout, ...$params],
            'metadata' => $meta,
        ]);

        $this->websocketConnection->sendText($requestJson);

        while (true) {
            // @phpstan-ignore-next-line
            $responseJson = $this->fetch($this->websocketConnection);

            /** @var array{id: string|null, guid: string|null, method: string|null, params: array{add: string|null, type: string|null, guid: string|null, initializer: array{mainFrame: array{guid: string}, opener: array{guid: string}}|null }, error: array{error: array{message: string|null}}} $response */
            $response = json_decode($responseJson, true);

            if (isset($response['error']['error']['message'])) {
                $message = $response['error']['error']['message'];

                if (str_contains($message, 'Playwright was just installed or updated')) {
                    throw new PlaywrightOutdatedException();
                }

                throw new ExpectationFailedException($message);
            }

            if (isset($response['method']) && $response['method'] === '__create__'
                && isset($response['params']['type']) && $response['params']['type'] === 'Page'
                && isset($response['guid'], $response['params']['guid'], $response['params']['initializer']['opener']['guid'])) {
                $this->handlePopupCreation($response['params']['initializer']['opener']['guid'], $response['params']['guid'], $response['params']['initializer']);
            }

            if (isset($response['method']) && $response['method'] === '__dispose__'
                && isset($response['guid']) && $this->getPage($response['guid']) instanceof Page) {
                $this->unregisterPage($response['guid']);
            }

            yield $response;

            if (
                (isset($response['id']) && $response['id'] === $requestId)
                || (isset($params['waitUntil']) && isset($response['params']['add']) && $params['waitUntil'] === $response['params']['add'])
            ) {
                break;
            }
        }
    }

    /**
     * Sets the timeout in milliseconds for requests.
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Returns the current timeout for requests.
     */
    public function timeout(): int
    {
        return $this->timeout;
    }

    /**
     * Registers the current page for event handling.
     */
    public function registerPage(string $guid, Page $page): void
    {
        $this->pages[$guid] = WeakReference::create($page);
    }

    /**
     * Removes page from event handling.
     */
    public function unregisterPage(string $guid): void
    {
        unset($this->pages[$guid]);
    }

    private function getPage(string $guid): ?Page
    {
        if (! array_key_exists($guid, $this->pages)) {
            return null;
        }

        return $this->pages[$guid]->get();
    }

    /**
     * Handles popup creation events.
     *
     * @param  array{mainFrame: array{guid: string}, opener: array{guid: string}}  $initializer
     */
    private function handlePopupCreation(string $openerGuid, string $popupGuid, array $initializer): void
    {
        $opener = $this->getPage($openerGuid);
        if ($opener instanceof Page && $opener->hasPendingPopup()) {
            $opener->handlePopupCreation($popupGuid, $initializer['mainFrame']['guid']);
        }
    }

    /**
     * Fetches the response from the Playwright server.
     */
    private function fetch(WebsocketConnection $client): string
    {
        return (string) $client->receive()?->read();
    }
}
