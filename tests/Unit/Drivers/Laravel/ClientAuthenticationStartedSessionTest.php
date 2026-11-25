<?php

declare(strict_types=1);

use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Pest\Browser\Drivers\ClientAuthentication;

it('keeps the unsaved session values of the test when the test changes the session after a frontend request', function (): void {
    $session = new Store('pest_session', new ArraySessionHandler(120));

    $session->start();
    $session->put('owner', 'previous');
    $session->save();

    $session->start();
    $session->put('owner', 'current');

    $authentication = new ClientAuthentication(null, $session);
    $authentication->isolate();

    $session->start();
    $session->put('client', 'frontend');
    $session->save();

    $authentication->restore();

    if (! $session->isStarted()) {
        $session->start();
    }

    $session->put('other', 'value');

    expect($session->get('owner'))->toBe('current');
});
