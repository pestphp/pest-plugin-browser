<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Amp\Cancellation;
use Amp\CancelledException;
use Amp\TimeoutCancellation;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
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
     * Seconds allowed on top of the timeout before a request is given up on.
     */
    private float $requestGraceSeconds = 30.0;

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
            // Playwright reads the timeout from the metadata since 1.62.0, where an
            // absent value means no timeout at all. Older servers read it from params.
            'metadata' => ['timeout' => $this->timeout, ...$meta],
        ]);

        $this->websocketConnection->sendText($requestJson);

        $allowance = ($this->timeout / 1000) + $this->requestGraceSeconds;
        $deadline = microtime(true) + $allowance;

        // The cancellation bounds a request the server never answers, and the deadline
        // bounds one that only ever receives unrelated messages. Neither covers both.
        $cancellation = new TimeoutCancellation($allowance);

        while (true) {
            if (microtime(true) > $deadline) {
                throw $this->unanswered($method, $allowance);
            }

            try {
                $responseJson = $this->fetch($this->websocketConnection, $cancellation);
            } catch (CancelledException) {
                throw $this->unanswered($method, $allowance);
            }

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
     * Builds the failure raised when a request is never answered.
     */
    private function unanswered(string $method, float $allowance): RuntimeException
    {
        return new RuntimeException(sprintf(
            'The Playwright server did not answer [%s] within %.1f seconds.',
            $method,
            $allowance,
        ));
    }

    /**
     * Fetches the response from the Playwright server.
     */
    private function fetch(WebsocketConnection $client, Cancellation $cancellation): string
    {
        $message = $client->receive($cancellation);

        // Without this, the null returned once the connection closes casts to an empty
        // string and leaves the caller waiting on a response that can never arrive.
        if (! $message instanceof WebsocketMessage) {
            throw new RuntimeException('The Playwright server closed the connection unexpectedly.');
        }

        return (string) $message->read($cancellation);
    }
}
