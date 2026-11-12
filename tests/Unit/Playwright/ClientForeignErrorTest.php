<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;

it('ignores an error answering another request and returns our response', function (): void {
    $connection = $this->createMock(WebsocketConnection::class);
    $requestId = null;
    $received = 0;

    $connection->expects($this->once())->method('sendText')->willReturnCallback(function (string $request) use (&$requestId): void {
        $requestId = json_decode($request, true, flags: JSON_THROW_ON_ERROR)['id'];
    });
    $connection->expects($this->exactly(2))->method('receive')->willReturnCallback(function () use (&$received, &$requestId): WebsocketMessage {
        return WebsocketMessage::fromText(json_encode(++$received === 1
            ? ['id' => 'abandoned-attempt', 'error' => ['error' => ['message' => 'Navigation aborted']]]
            : ['id' => $requestId, 'result' => []], JSON_THROW_ON_ERROR));
    });

    $client = new Client;
    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    $responses = iterator_to_array($client->execute('main-frame', 'goto', ['waitUntil' => 'load']));

    expect($responses[array_key_last($responses)]['id'])->toBe($requestId);
});
