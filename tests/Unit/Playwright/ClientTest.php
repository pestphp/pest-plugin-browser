<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;

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
