<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Playwright\BrowserFactory;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Playwright;
use PHPUnit\Framework\ExpectationFailedException;

it('uses a launch timeout without changing the action timeout', function (int $actionTimeout, int $launchTimeout): void {
    $instance = new ReflectionProperty(Client::class, 'instance');
    $browserTypes = new ReflectionProperty(Playwright::class, 'browserTypes');
    $previousClient = $instance->getValue();
    $previousBrowserTypes = $browserTypes->getValue();
    $requests = [];
    $responses = [];

    $connection = $this->createStub(WebsocketConnection::class);
    $connection->method('sendText')->willReturnCallback(function (string $json) use (&$requests, &$responses): void {
        $request = json_decode($json, true);
        $requests[] = $request;

        if ($request['method'] === 'initialize') {
            $responses[] = ['method' => '__create__', 'params' => [
                'type' => 'BrowserType',
                'guid' => 'browserType@chromium',
                'initializer' => ['name' => 'chromium'],
            ]];
        }

        $responses[] = ['id' => $request['id'], 'result' => []];
    });
    $connection->method('receive')->willReturnCallback(
        function () use (&$responses): WebsocketMessage {
            return WebsocketMessage::fromText((string) json_encode(array_shift($responses)));
        },
    );

    $client = new Client();
    $client->setTimeout($actionTimeout);
    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    try {
        $instance->setValue($client);
        $browserTypes->setValue([]);

        expect(Playwright::browser(BrowserType::CHROME))->toBeInstanceOf(BrowserFactory::class);

        iterator_to_array($client->execute('page@1', 'goto'));

        expect($requests[0]['metadata']['timeout'])->toBe($launchTimeout)
            ->and($requests[0]['params']['timeout'])->toBe($launchTimeout)
            ->and($requests[1]['metadata']['timeout'])->toBe($actionTimeout)
            ->and($requests[1]['params']['timeout'])->toBe($actionTimeout)
            ->and($client->timeout())->toBe($actionTimeout);
    } finally {
        $instance->setValue($previousClient);
        $browserTypes->setValue($previousBrowserTypes);
    }
})->with([
    'short action timeout' => [2_000, 30_000],
    'longer configured timeout' => [45_000, 45_000],
]);

it('discards partial initialization and closes the failed connection', function (): void {
    $instance = new ReflectionProperty(Client::class, 'instance');
    $browserTypes = new ReflectionProperty(Playwright::class, 'browserTypes');
    $previousClient = $instance->getValue();
    $previousBrowserTypes = $browserTypes->getValue();
    $responses = [
        ['method' => '__create__', 'params' => [
            'type' => 'BrowserType',
            'guid' => 'browserType@chromium',
            'initializer' => ['name' => 'chromium'],
        ]],
        ['error' => ['error' => ['message' => 'Timeout 30000ms exceeded.']]],
    ];

    $connection = $this->createMock(WebsocketConnection::class);
    $connection->method('receive')->willReturnCallback(
        function () use (&$responses): WebsocketMessage {
            return WebsocketMessage::fromText((string) json_encode(array_shift($responses)));
        },
    );
    $connection->method('isClosed')->willReturn(false);
    $connection->expects($this->once())->method('close');

    $client = new Client();
    $client->setTimeout(2_000);
    $websocket = new ReflectionProperty(Client::class, 'websocketConnection');
    $websocket->setValue($client, $connection);

    try {
        $instance->setValue($client);
        $browserTypes->setValue([]);

        expect(fn (): BrowserFactory => Playwright::browser(BrowserType::CHROME))
            ->toThrow(ExpectationFailedException::class, 'Timeout 30000ms exceeded.');

        expect($browserTypes->getValue())->toBe([])
            ->and($websocket->getValue($client))->toBeNull()
            ->and($client->timeout())->toBe(2_000);
    } finally {
        $instance->setValue($previousClient);
        $browserTypes->setValue($previousBrowserTypes);
    }
});
