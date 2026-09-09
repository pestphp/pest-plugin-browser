<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Context;
use Pest\Browser\Playwright\Page;

function pageWithStubbedWaitClient(WebsocketConnection $connection): Page
{
    $client = Client::instance();
    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    $context = new ReflectionClass(Context::class)->newInstanceWithoutConstructor();

    return new Page($context, 'page@1', 'frame@1');
}

it('sends waitForLoadState to Playwright', function (): void {
    $sent = null;
    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });
    $connection->method('receive')->willReturnCallback(function () use (&$sent): WebsocketMessage {
        return WebsocketMessage::fromText((string) json_encode(['id' => $sent['id']]));
    });

    pageWithStubbedWaitClient($connection)->waitForLoadState('networkidle');

    expect($sent)->toBeArray()
        ->and($sent['guid'])->toBe('page@1')
        ->and($sent['method'])->toBe('waitForLoadState')
        ->and($sent['params']['state'])->toBe('networkidle');
});

it('sends waitForFunction to Playwright', function (): void {
    $sent = null;
    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });
    $connection->method('receive')->willReturnCallback(function () use (&$sent): WebsocketMessage {
        return WebsocketMessage::fromText((string) json_encode(['id' => $sent['id']]));
    });

    pageWithStubbedWaitClient($connection)->waitForFunction('() => window.ready === true');

    expect($sent)->toBeArray()
        ->and($sent['guid'])->toBe('page@1')
        ->and($sent['method'])->toBe('waitForFunction');
});

it('sends waitForURL to Playwright', function (): void {
    $sent = null;
    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('sendText')->willReturnCallback(function (string $payload) use (&$sent): void {
        $sent = json_decode($payload, true);
    });
    $connection->method('receive')->willReturnCallback(function () use (&$sent): WebsocketMessage {
        return WebsocketMessage::fromText((string) json_encode(['id' => $sent['id']]));
    });

    pageWithStubbedWaitClient($connection)->waitForURL('https://example.com/dashboard');

    expect($sent)->toBeArray()
        ->and($sent['guid'])->toBe('page@1')
        ->and($sent['method'])->toBe('waitForURL')
        ->and($sent['params']['url'])->toBe('https://example.com/dashboard');
});
