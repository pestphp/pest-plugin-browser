<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Playwright\Client;

it('can open a page after the Playwright connection closes', function (): void {
    Route::get('/', fn (): string => '<div>Welcome</div>');

    visit('/')->assertSee('Welcome');

    $connection = new ReflectionProperty(Client::class, 'websocketConnection')->getValue(Client::instance());

    assert($connection instanceof WebsocketConnection);

    $connection->close();

    $this->__markAsBrowserTest();

    visit('/')->assertSee('Welcome');
});
