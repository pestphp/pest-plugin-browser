<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Amp\Websocket\Client\WebsocketConnection;
use Generator;
use Pest\Browser\Api\DownloadCollector;
use Pest\Browser\Api\PendingDownload;
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
     * Pending downloads awaiting resolution, keyed by page GUID.
     *
     * @var array<string, PendingDownload>
     */
    private array $pendingDownloads = [];

    /**
     * Download collectors for capturing multiple downloads, keyed by page GUID.
     *
     * @var array<string, DownloadCollector>
     */
    private array $downloadCollectors = [];

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
            $responseJson = $this->fetch($this->connection());

            /** @var array{id?: string, method?: string, guid?: string, params?: array<string, mixed>, result?: array<string, mixed>, error?: array{error?: array{message?: string}}} $response */
            $response = (array) json_decode($responseJson, true);

            $this->handleError($response);
            $this->handleDownload($response);

            yield $response;

            if ($this->isResponseComplete($response, $requestId, $params)) {
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
     * Registers a pending download for the given page.
     */
    public function expectDownload(string $pageGuid, PendingDownload $download): void
    {
        $this->pendingDownloads[$pageGuid] = $download;
    }

    /**
     * Starts collecting downloads for the given page.
     */
    public function startCollectingDownloads(string $pageGuid, DownloadCollector $collector): void
    {
        $this->downloadCollectors[$pageGuid] = $collector;
    }

    /**
     * Stops collecting downloads for the given page.
     */
    public function stopCollectingDownloads(string $pageGuid): void
    {
        unset($this->downloadCollectors[$pageGuid]);
    }

    /**
     * Handles error responses from Playwright.
     *
     * @param  array<string, mixed>  $response
     */
    private function handleError(array $response): void
    {
        $error = $response['error'] ?? null;
        $errorInner = is_array($error) ? ($error['error'] ?? null) : null;
        $errorMessage = is_array($errorInner) ? ($errorInner['message'] ?? null) : null;

        if (! is_string($errorMessage)) {
            return;
        }

        if (str_contains($errorMessage, 'Playwright was just installed or updated')) {
            throw new PlaywrightOutdatedException();
        }

        throw new ExpectationFailedException($errorMessage);
    }

    /**
     * Handles download events from Playwright.
     *
     * @param  array<string, mixed>  $response
     */
    private function handleDownload(array $response): void
    {
        $event = DownloadEvent::fromResponse($response);

        if ($event === null) {
            return;
        }

        $collector = $this->downloadCollectors[$event->pageGuid] ?? null;

        if ($collector !== null) {
            $collector->add($event->url, $event->suggestedFilename, $event->artifactGuid);

            return;
        }

        $download = $this->pendingDownloads[$event->pageGuid] ?? null;

        if ($download !== null) {
            $download->resolve($event->url, $event->suggestedFilename, $event->artifactGuid);
            unset($this->pendingDownloads[$event->pageGuid]);
        }
    }

    /**
     * Determines if the response completes the current request.
     *
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $params
     */
    private function isResponseComplete(array $response, string $requestId, array $params): bool
    {
        if (isset($response['id']) && $response['id'] === $requestId) {
            return true;
        }

        $responseParams = $response['params'] ?? null;
        $responseParamsAdd = is_array($responseParams) ? ($responseParams['add'] ?? null) : null;

        return isset($params['waitUntil']) && $params['waitUntil'] === $responseParamsAdd;
    }

    /**
     * Returns the active WebSocket connection.
     */
    private function connection(): WebsocketConnection
    {
        assert($this->websocketConnection instanceof WebsocketConnection);

        return $this->websocketConnection;
    }

    /**
     * Fetches the response from the Playwright server.
     */
    private function fetch(WebsocketConnection $client): string
    {
        return (string) $client->receive()?->read();
    }
}
