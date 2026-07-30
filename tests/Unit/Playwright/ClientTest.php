<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;

it('reports a closed connection instead of looping on it', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('receive')->willReturn(null);

    $client = new Client();
    $client->setTimeout(100);

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);
    new ReflectionProperty(Client::class, 'requestGraceSeconds')->setValue($client, 0.2);

    expect(fn (): array => iterator_to_array($client->execute('page@1', 'goto')))
        ->toThrow(RuntimeException::class, 'The Playwright server closed the connection unexpectedly.');
});

it('gives up on a request that only ever receives unrelated messages', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('receive')->willReturnCallback(
        fn (): WebsocketMessage => WebsocketMessage::fromText('{"guid":"page@1","method":"console"}')
    );

    $client = new Client();
    $client->setTimeout(100);

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);
    new ReflectionProperty(Client::class, 'requestGraceSeconds')->setValue($client, 0.2);

    expect(fn (): array => iterator_to_array($client->execute('page@1', 'goto')))
        ->toThrow('The Playwright server did not answer [goto]');
});

it('returns the response matching the request id', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);

    $sent = null;
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });

    $connection->method('receive')->willReturnCallback(function () use (&$sent): WebsocketMessage {
        return WebsocketMessage::fromText((string) json_encode([
            'id' => $sent['id'],
            'result' => ['value' => 'pong'],
        ]));
    });

    $client = new Client();

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    $messages = iterator_to_array($client->execute('page@1', 'goto'));

    expect($messages)->toHaveCount(1)
        ->and($messages[0]['result']['value'])->toBe('pong');
});

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
