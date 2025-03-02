<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
use RuntimeException;
use WebSocket\Client as WebSocketClient;

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
     * WebSocket client.
     */
    private readonly WebSocketClient $websocketClient;

    /**
     * Constructs new client.
     */
    public function __construct(string $url)
    {
        $this->websocketClient = new WebSocketClient($url);
    }

    /**
     * Destructs the client and stops the WebSocket connection.
     */
    public function __destruct()
    {
        $this->stop();
    }

    /**
     * Creates a new client instance.
     */
    public static function listen(string $url): self
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self($url);
        }

        return self::$instance;
    }

    /**
     * Returns the current client instance.
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * Executes a method on the Playwright instance.
     *
     * @return Generator<array<string, mixed>>
     */
    public function execute(string $guid, string $method, array $params = [], array $meta = []): Generator
    {
        $requestId = uniqid();

        $requestJson = json_encode([
            'id' => $requestId,
            'guid' => $guid,
            'method' => $method,
            'params' => $params,
            'metadata' => $meta,
        ]);

        // file_put_contents('log.log', $requestJson.PHP_EOL, FILE_APPEND);

        $this->websocketClient->text($requestJson);

        while (true) {
            $responseJson = $this->websocketClient->receive();
            $response = json_decode((string) $responseJson, true);

            if (isset($response['error']['error']['message'])) {
                throw new RuntimeException($response['error']['error']['message']);
            }

            // file_put_contents('log.log', $responseJson.PHP_EOL, FILE_APPEND);

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
     * Closes the WebSocket connection.
     */
    public function stop(): void
    {
        $this->websocketClient->close();
    }
}
