<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketConnector;
use Amp\Websocket\Client\WebsocketHandshake;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Playwright;
use PHPUnit\Framework\ExpectationFailedException;

use function Amp\Websocket\Client\websocketConnector;

it('connects using local addresses or complete remote WebSocket endpoints', function (string $endpoint, string $expected): void {
    $original = websocketConnector();
    $connector = $this->createMock(WebsocketConnector::class);
    $connection = $this->createStub(WebsocketConnection::class);

    $connector->expects($this->once())->method('connect')->willReturnCallback(
        function (WebsocketHandshake $handshake) use ($expected, $connection): WebsocketConnection {
            $uri = $handshake->getUri();
            parse_str($uri->getQuery(), $query);

            expect((string) $uri)->toStartWith($expected)
                ->and($query['browser'])->toBe(Playwright::defaultBrowserType()->toPlaywrightName())
                ->and(json_decode($query['launch-options'], true, flags: JSON_THROW_ON_ERROR))->toBe([
                    'headless' => Playwright::isHeadless(),
                    'slowMo' => Playwright::slowMo(),
                    'ignoreHTTPSErrors' => true,
                    'bypassCSP' => true,
                ]);

            return $connection;
        },
    );

    websocketConnector($connector);

    try {
        $client = new Client;
        $client->connectTo($endpoint);
    } finally {
        websocketConnector($original);
    }
})->with([
    ['127.0.0.1:3000', 'ws://127.0.0.1:3000?browser='],
    ['ws://example.test:3000/session', 'ws://example.test:3000/session?browser='],
    ['wss://example.test/session?token=abc%2B123', 'wss://example.test/session?token=abc%2B123&browser='],
]);

it('reports a closed connection instead of waiting on it', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('receive')->willReturn(null);

    $client = new Client();

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    expect(fn (): array => iterator_to_array($client->execute('page@1', 'goto')))
        ->toThrow(RuntimeException::class, 'The Playwright server closed the connection unexpectedly.');
});

it('still completes a request the server answers', function (): void {
    $sent = null;

    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('sendText')->willReturnCallback(function (string $json) use (&$sent): void {
        $sent = json_decode($json, true);
    });
    $connection->method('receive')->willReturnCallback(
        function () use (&$sent): WebsocketMessage {
            return WebsocketMessage::fromText((string) json_encode([
                'id' => $sent['id'],
                'result' => ['value' => 'done'],
            ]));
        },
    );

    $client = new Client();

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    $responses = iterator_to_array($client->execute('page@1', 'goto'));

    expect($responses)->toHaveCount(1)
        ->and($responses[0]['result']['value'])->toBe('done');
});

it('reports the screenshot comparison result instead of the bare expect error', function (): void {
    $sent = null;

    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('sendText')->willReturnCallback(function (string $json) use (&$sent): void {
        $sent = json_decode($json, true);
    });
    $connection->method('receive')->willReturnCallback(
        function () use (&$sent): WebsocketMessage {
            return WebsocketMessage::fromText((string) json_encode([
                'id' => $sent['id'],
                'error' => ['error' => ['message' => 'Expect failed', 'name' => 'Error']],
                'errorDetails' => ['customErrorMessage' => '1954 pixels (ratio 0.01 of all image pixels) are different.'],
            ]));
        },
    );

    $client = new Client();

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    expect(fn (): array => iterator_to_array($client->execute('page@1', 'expectScreenshot')))
        ->toThrow(ExpectationFailedException::class, '1954 pixels (ratio 0.01 of all image pixels) are different.');
});
