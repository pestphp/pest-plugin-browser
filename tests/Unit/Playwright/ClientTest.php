<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;

it('sends the timeout as metadata so the server enforces it', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);

    $sent = null;
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });

    $connection->method('receive')->willReturnCallback(function () use (&$sent): WebsocketMessage {
        return WebsocketMessage::fromText((string) json_encode(['id' => $sent['id']]));
    });

    $client = new Client();
    $client->setTimeout(100);

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    iterator_to_array($client->execute('page@1', 'click'));

    expect($sent['metadata']['timeout'])->toBe(100)
        ->and($sent['params']['timeout'])->toBe(100);
});
