<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Pest\Browser\Exceptions\ServerNotFoundException;
use Pest\Browser\Playwright\Client;

test('a connection that closes before the response ends the wait', function (): void {
    $connection = $this->createMock(WebsocketConnection::class);
    $connection->method('receive')->willReturn(null);

    $client = new Client;
    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    iterator_to_array($client->execute('guid', 'method'));
})->throws(ServerNotFoundException::class, 'closed the connection');
