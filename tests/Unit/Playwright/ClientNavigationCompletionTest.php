<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;
use PHPUnit\Framework\ExpectationFailedException;

it('waits for the navigation response after load events from other documents', function (string $method): void {
    $connection = $this->createMock(WebsocketConnection::class);
    $requestId = null;
    $received = 0;

    $connection->expects($this->once())->method('sendText')->willReturnCallback(function (string $request) use (&$requestId): void {
        $requestId = json_decode($request, true, flags: JSON_THROW_ON_ERROR)['id'];
    });
    $connection->expects($this->exactly(3))->method('receive')->willReturnCallback(function () use (&$received, &$requestId): WebsocketMessage {
        $received++;

        return WebsocketMessage::fromText(json_encode(match ($received) {
            1 => ['guid' => 'initial-document', 'method' => 'loadstate', 'params' => ['add' => 'load']],
            2 => ['guid' => 'child-frame', 'method' => 'loadstate', 'params' => ['add' => 'load']],
            default => ['id' => $requestId, 'result' => []],
        }, JSON_THROW_ON_ERROR));
    });

    $client = new Client;
    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    $responses = iterator_to_array($client->execute('main-frame', $method, ['waitUntil' => 'load']));

    expect($received)->toBe(3)
        ->and($responses[array_key_last($responses)]['id'])->toBe($requestId);
})->with(['goto', 'reload']);

it('reports a navigation failure even when a load event arrived first', function (): void {
    $connection = $this->createMock(WebsocketConnection::class);
    $requestId = null;
    $received = 0;

    $connection->expects($this->once())->method('sendText')->willReturnCallback(function (string $request) use (&$requestId): void {
        $requestId = json_decode($request, true, flags: JSON_THROW_ON_ERROR)['id'];
    });
    $connection->expects($this->exactly(2))->method('receive')->willReturnCallback(function () use (&$received, &$requestId): WebsocketMessage {
        return WebsocketMessage::fromText(json_encode(++$received === 1
            ? ['guid' => 'initial-document', 'method' => 'loadstate', 'params' => ['add' => 'load']]
            : ['id' => $requestId, 'error' => ['error' => ['message' => 'Navigation failed']]], JSON_THROW_ON_ERROR));
    });

    $client = new Client;
    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    expect(fn (): array => iterator_to_array($client->execute('main-frame', 'goto', ['waitUntil' => 'load'])))
        ->toThrow(ExpectationFailedException::class, 'Navigation failed');
});
