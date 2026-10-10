<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Runs one request against a scripted socket and returns what `execute()` yields.
 *
 * @param  Closure(string): list<array<string, mixed>>  $messages  the messages Playwright sends after the request with the given id
 * @return list<array<string, mixed>>
 */
function executeAgainst(Closure $messages): array
{
    $queue = [];

    $connection = Mockery::mock(WebsocketConnection::class);
    $connection->shouldReceive('sendText')->once()->andReturnUsing(function (string $request) use (&$queue, $messages): void {
        /** @var array{id: string} $decoded */
        $decoded = json_decode($request, true, flags: JSON_THROW_ON_ERROR);

        $queue = $messages($decoded['id']);
    });
    $connection->shouldReceive('receive')->andReturnUsing(
        function () use (&$queue): WebsocketMessage {
            return WebsocketMessage::fromText(json_encode(array_shift($queue), JSON_THROW_ON_ERROR));
        },
    );

    $client = Client::instance();
    $property = new ReflectionProperty(Client::class, 'websocketConnection');
    $previous = $property->getValue($client);
    $property->setValue($client, $connection);

    try {
        return iterator_to_array($client->execute('frame-guid', 'evaluateExpression', ['expression' => '1 + 1']), false);
    } finally {
        $property->setValue($client, $previous);
    }
}

test('replies to earlier requests neither fail nor answer the current request', function (): void {
    $requestId = null;

    $yielded = executeAgainst(function (string $id) use (&$requestId): array {
        $requestId = $id;

        return [
            ['id' => 'reload-that-returned-on-its-load-event', 'error' => ['error' => ['message' => 'Timeout 1000ms exceeded.']]],
            ['id' => 'abandoned-attempt', 'result' => ['value' => ['n' => 200]]],
            ['guid' => 'frame-guid', 'method' => 'loadstate', 'params' => ['add' => 'load']],
            ['id' => $id, 'result' => ['value' => ['n' => 2]]],
        ];
    });

    expect($yielded)->toBe([
        ['guid' => 'frame-guid', 'method' => 'loadstate', 'params' => ['add' => 'load']],
        ['id' => $requestId, 'result' => ['value' => ['n' => 2]]],
    ]);
});

test('an error reply to the current request still fails it', function (): void {
    executeAgainst(fn (string $id): array => [
        ['id' => $id, 'error' => ['error' => ['message' => 'Execution context was destroyed']]],
    ]);
})->throws(ExpectationFailedException::class, 'Execution context was destroyed');
