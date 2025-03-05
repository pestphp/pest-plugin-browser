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
        if (! $this->websocketClient) {
            $this->websocketClient = new WebSocketClient($url);
        }
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

        //file_put_contents('log.log', $requestJson.PHP_EOL, FILE_APPEND);

        $this->websocketClient->text($requestJson);

        while (true) {
            $responseJson = $this->websocketClient->receive();
            $response = json_decode((string) $responseJson, true);

            if (isset($response['error']['error']['message'])) {
                throw new RuntimeException($response['error']['error']['message']);
            }

            //file_put_contents('log.log', $responseJson.PHP_EOL, FILE_APPEND);

            yield $response;

            if (
                (isset($response['id']) && $response['id'] === $requestId)
                || (isset($params['waitUntil']) && isset($response['params']['add']) && $params['waitUntil'] === $response['params']['add'])
            ) {
                break;
            }
        }
    }
}
