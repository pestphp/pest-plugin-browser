<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Amp\Websocket\Client\WebsocketConnection;
use Generator;
use Pest\Browser\Exceptions\PlaywrightOutdatedException;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;

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
        $startTime = hrtime(true);
        $operationTimeout = $params['timeout'] ?? $this->timeout;
        $maxWaitTimeNs = $operationTimeout * 1_000_000; // Convert ms to ns

        $requestJson = (string) json_encode([
            'id' => $requestId,
            'guid' => $guid,
            'method' => $method,
            'params' => ['timeout' => $this->timeout, ...$params],
            'metadata' => $meta,
        ]);

        $this->websocketConnection->sendText($requestJson);

        while (true) {
            // Check for timeout to prevent infinite loops
            $elapsed = hrtime(true) - $startTime;
            if ($elapsed > $maxWaitTimeNs) {
                throw new RuntimeException(
                    "Playwright operation '$method' timed out after ".round($elapsed / 1_000_000_000, 2).' seconds'
                );
            }

            $responseJson = $this->fetch($this->websocketConnection);

            // Handle null/empty responses (WebSocket connection lost)
            if ($responseJson === null || $responseJson === '') {
                throw new RuntimeException(
                    "WebSocket connection lost while executing '$method' on '$guid'"
                );
            }

            /** @var array{id: string|null, params: array{add: string|null}, error: array{error: array{message: string|null}}}|null $response */
            $response = json_decode($responseJson, true);

            // Handle JSON decode failure
            if ($response === null) {
                throw new RuntimeException(
                    'Invalid JSON response from Playwright server: '.substr($responseJson, 0, 100)
                );
            }

            if (isset($response['error']['error']['message'])) {
                $message = $response['error']['error']['message'];

                if (str_contains($message, 'Playwright was just installed or updated')) {
                    throw new PlaywrightOutdatedException();
                }

                throw new ExpectationFailedException($message);
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
     * Fetches the response from the Playwright server.
     *
     * Returns null if the WebSocket connection is closed.
     */
    private function fetch(WebsocketConnection $client): ?string
    {
        $message = $client->receive();

        if ($message === null) {
            return null; // Connection closed
        }

        return $message->read();
    }
}
