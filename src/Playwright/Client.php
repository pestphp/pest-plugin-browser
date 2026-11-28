<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Generator;
use Pest\Browser\Exceptions\PlaywrightOutdatedException;
use Pest\Browser\Support\JavaScriptSerializer;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;
use Throwable;

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
     * The PHP call stacks of the requests made while tracing, keyed by request id.
     *
     * @var array<string, array<int, array{file: string, line: int, function: string}>>
     */
    private array $stacks = [];

    /**
     * Ids of the binding call resolutions sent for browser reports, whose responses are skipped.
     *
     * @var array<string, true>
     */
    private array $reportResolutions = [];

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
     *
     * A connection the server has closed is replaced, together with the browsers
     * launched on it, so one lost connection fails only the test that was using it.
     */
    public function connectTo(string $url): void
    {
        if ($this->websocketConnection?->isClosed() === true) {
            $this->websocketConnection = null;

            Playwright::forget();
        }

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
        $requestId = uniqid();

        if (Tracing::isRecording()) {
            $this->recordStack($requestId);
        }

        assert($this->websocketConnection instanceof WebsocketConnection, 'WebSocket client is not connected.');

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
            $decoded = json_decode($responseJson, true);

            if (is_array($decoded) && $this->handleInternalMessage($decoded)) {
                continue;
            }

            /** @var array{id: string|null, params: array{add: string|null}, error: array{error: array{message: string|null}}, errorDetails: array{customErrorMessage: string|null}} $response */
            $response = $decoded;

            // A response addressed to another request was stranded by a generator
            // abandoned before its final frame was read — a caller that returns on
            // a __create__ event, such as querySelector() or evaluateHandle(), leaves
            // its response unconsumed. Processing it here would desynchronize the
            // stream, and an error in that stranded response would fail this command.
            if (isset($response['id']) && $response['id'] !== $requestId) {
                continue;
            }

            if (isset($response['error']['error']['message'])) {
                $message = $response['error']['error']['message'];

                if (str_contains($message, 'Playwright was just installed or updated')) {
                    throw new PlaywrightOutdatedException();
                }

                // Since 1.61 a failed screenshot expectation reports only "Expect failed"
                // here, and sends what actually differs in the error details instead.
                if (is_string($response['errorDetails']['customErrorMessage'] ?? null)) {
                    $message = $response['errorDetails']['customErrorMessage'];
                }

                throw new ExpectationFailedException($message);
            }

            yield $response;

            if (isset($response['id']) && $response['id'] === $requestId) {
                break;
            }
        }
    }

    /**
     * Drops the current connection so the next test can initialize again.
     *
     * Playwright accepts initialize only once per socket, and marks the
     * socket initialized before the browser launch finishes.
     */
    public function disconnect(): void
    {
        if (! $this->websocketConnection instanceof WebsocketConnection) {
            return;
        }

        $connection = $this->websocketConnection;
        $this->websocketConnection = null;

        if ($connection->isClosed()) {
            return;
        }

        try {
            $connection->close();
        } catch (Throwable) {
            // The socket can already be gone when initialize fails.
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
     * Returns the PHP call stacks of the requests made while tracing.
     *
     * @return array<string, array<int, array{file: string, line: int, function: string}>>
     */
    public function stacks(): array
    {
        return $this->stacks;
    }

    /**
     * Forgets the PHP call stacks of the requests made while tracing.
     */
    public function flushStacks(): void
    {
        $this->stacks = [];
    }

    /**
     * Records the PHP call stack of the given request, so the trace viewer can show the test's source.
     */
    private function recordStack(string $requestId): void
    {
        $src = dirname(__DIR__);
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $frames = [];

        foreach ($backtrace as $index => $trace) {
            if (! isset($trace['file'], $trace['line'])) {
                continue;
            }

            if (str_starts_with($trace['file'], $src) || str_contains($trace['file'], DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $frames[] = [
                'file' => $trace['file'],
                'line' => $trace['line'],
                'function' => $backtrace[$index + 1]['function'] ?? '',
            ];
        }

        if ($frames !== []) {
            $this->stacks[$requestId] = $frames;
        }
    }

    /**
     * Consumes the messages the client handles itself: browser reports and their resolutions.
     *
     * @param  array<mixed>  $message
     */
    private function handleInternalMessage(array $message): bool
    {
        $id = $message['id'] ?? null;

        if (is_string($id) && isset($this->reportResolutions[$id])) {
            unset($this->reportResolutions[$id]);

            return true;
        }

        $params = $message['params'] ?? null;

        if (($message['method'] ?? null) !== '__create__'
            || ! is_array($params)
            || ($params['type'] ?? null) !== 'BindingCall'
            || ! is_string($params['guid'] ?? null)
            || ! is_array($params['initializer'] ?? null)
            || ($params['initializer']['name'] ?? null) !== InitScript::BINDING_NAME) {
            return false;
        }

        $initializer = $params['initializer'];
        $frameGuid = is_array($initializer['frame'] ?? null) ? ($initializer['frame']['guid'] ?? null) : null;
        $entry = JavaScriptSerializer::parseValue(is_array($initializer['args'] ?? null) ? ($initializer['args'][0] ?? null) : null);

        if (is_string($frameGuid) && is_array($entry)) {
            /** @var array<string, mixed> $entry */
            BrowserLogCollector::add($frameGuid, $entry);
        }

        $requestId = uniqid();
        $this->reportResolutions[$requestId] = true;

        $this->websocketConnection?->sendText((string) json_encode([
            'id' => $requestId,
            'guid' => $params['guid'],
            'method' => 'resolve',
            'params' => ['result' => JavaScriptSerializer::serializeArgument(null)],
            'metadata' => ['timeout' => $this->timeout],
        ]));

        return true;
    }

    /**
     * Fetches the response from the Playwright server.
     */
    private function fetch(WebsocketConnection $client): string
    {
        $message = $client->receive();

        // Once the connection closes, receive() returns null. Cast to an empty string,
        // that response has neither an `error` nor an `id`, so execute() would keep
        // waiting on a connection that can never answer, at full CPU.
        if (! $message instanceof WebsocketMessage) {
            $closeInfo = $client->getCloseInfo();

            throw new RuntimeException(sprintf(
                'The Playwright server closed the connection unexpectedly (code %d: %s).',
                $closeInfo->getCode(),
                $closeInfo->getReason(),
            ));
        }

        return (string) $message->read();
    }
}
