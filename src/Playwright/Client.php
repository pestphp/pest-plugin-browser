<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Amp\Websocket\Client\WebsocketConnection;
use Generator;
use Pest\Browser\Exceptions\PlaywrightOutdatedException;
use PHPUnit\Framework\ExpectationFailedException;

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
                'slowMo' => Playwright::slowMo(),
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

        $timeout = is_numeric($params['timeout'] ?? null) ? (int) $params['timeout'] : $this->timeout;

        $requestJson = (string) json_encode([
            'id' => $requestId,
            'guid' => $guid,
            'method' => $method,
            // Playwright reads the action timeout from the metadata since 1.62, and read it
            // from the params before that. Both are validated with a schema that silently
            // drops unknown keys, so sending it twice also covers an older install.
            'params' => ['timeout' => $timeout, ...$params],
            'metadata' => ['timeout' => $timeout, ...$meta],
        ]);

        $this->websocketConnection->sendText($requestJson);

        while (true) {
            $responseJson = $this->fetch($this->websocketConnection);
            /** @var array{id: string|null, params: array{add: string|null}, error: array{error: array{message: string|null}}} $response */
            $response = json_decode($responseJson, true);

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
     */
    private function fetch(WebsocketConnection $client): string
    {
        return (string) $client->receive()?->read();
    }
}
