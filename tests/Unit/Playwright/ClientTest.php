<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;
use PHPUnit\Framework\ExpectationFailedException;

it('drops responses addressed to another request', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);

    $sent = null;
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });

    $frames = [
        ['id' => 'stranded-request', 'result' => ['value' => 'stale']],
    ];

    $connection->method('receive')->willReturnCallback(function () use (&$frames, &$sent): WebsocketMessage {
        $frame = array_shift($frames) ?? ['id' => $sent['id']];

        return WebsocketMessage::fromText((string) json_encode($frame));
    });

    $client = new Client();

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    $messages = iterator_to_array($client->execute('page@1', 'evaluateExpression'));

    expect($messages)->toHaveCount(1)
        ->and($messages[0]['id'])->not->toBe('stranded-request');
});

it('does not fail the current request on an error frame addressed to another one', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);

    $sent = null;
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });

    $frames = [
        ['id' => 'stranded-goto', 'error' => ['error' => ['message' => 'Target page, context or browser has been closed']]],
    ];

    $connection->method('receive')->willReturnCallback(function () use (&$frames, &$sent): WebsocketMessage {
        $frame = array_shift($frames) ?? ['id' => $sent['id']];

        return WebsocketMessage::fromText((string) json_encode($frame));
    });

    $client = new Client();

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    $messages = iterator_to_array($client->execute('browser@1', 'newContext'));

    expect($messages)->toHaveCount(1)
        ->and($messages[0]['id'])->not->toBe('stranded-goto');
});

it('still fails the current request on its own error frame', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);

    $sent = null;
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });

    $connection->method('receive')->willReturnCallback(function () use (&$sent): WebsocketMessage {
        return WebsocketMessage::fromText((string) json_encode([
            'id' => $sent['id'],
            'error' => ['error' => ['message' => 'Target page, context or browser has been closed']],
        ]));
    });

    $client = new Client();

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    expect(fn (): array => iterator_to_array($client->execute('page@1', 'click')))
        ->toThrow(ExpectationFailedException::class, 'Target page, context or browser has been closed');
});

it('still yields event frames, which carry no id', function (): void {
    $connection = $this->createStub(WebsocketConnection::class);

    $sent = null;
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });

    $frames = [
        ['guid' => 'frame@1', 'method' => 'navigated', 'params' => ['url' => 'http://127.0.0.1/']],
    ];

    $connection->method('receive')->willReturnCallback(function () use (&$frames, &$sent): WebsocketMessage {
        $frame = array_shift($frames) ?? ['id' => $sent['id']];

        return WebsocketMessage::fromText((string) json_encode($frame));
    });

    $client = new Client();

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    $messages = iterator_to_array($client->execute('frame@1', 'goto', ['url' => 'http://127.0.0.1/']));

    expect($messages)->toHaveCount(2)
        ->and($messages[0]['method'])->toBe('navigated');
});
