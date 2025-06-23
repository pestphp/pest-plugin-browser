<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
use PHPUnit\Framework\ExpectationFailedException;
use React\EventLoop\Loop;
use WebSocket\Client as WebSocketClient;
use WebSocket\Exception\ConnectionTimeoutException;

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
    private ?WebSocketClient $websocketClient = null;

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
        if (! $this->websocketClient instanceof WebSocketClient) {
            $this->websocketClient = new WebSocketClient("ws://$url?browser=chromium");
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
        assert($this->websocketClient instanceof WebSocketClient, 'WebSocket client is not connected.');

        $requestId = uniqid();

        $requestJson = (string) json_encode([
            'id' => $requestId,
            'guid' => $guid,
            'method' => $method,
            'params' => ['timeout' => 5_000, ...$params],
            'metadata' => $meta,
        ]);

        $this->websocketClient->text($requestJson);

        while (true) {
            /** @var string $responseJson */
            $responseJson = $this->fetch($this->websocketClient);

            /** @var array{id: string|null, params: array{add: string|null}, error: array{error: array{message: string|null}}} $response */
            $response = json_decode($responseJson, true);

            if (isset($response['error']['error']['message'])) {
                $message = $response['error']['error']['message'];

                if (str_contains($message, 'npx playwright install')) {
                    $message = 'Playwright was not installed. Please run [npx playwright install] to install the browsers.';
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
     * Fetches the response from the Playwright server.
     */
    private function fetch(WebSocketClient $client): string
    {
        $client->setTimeout(0.01);

        while (true) {
            try {
                return $client->receive()->getContent();
            } catch (ConnectionTimeoutException) {
                Loop::futureTick(fn () => Loop::stop());
                Loop::run();
            }
        }
    }
}
