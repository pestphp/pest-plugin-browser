<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Servers\PlaywrightNpmServer;
use Pest\Browser\Support\PackageJsonDirectory;
use Pest\Browser\Support\Port;

it('stops the playwright server process itself, not only its shell', function (): void {
    $port = Port::find();

    $server = PlaywrightNpmServer::create(
        PackageJsonDirectory::find(),
        '.'.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'.bin'.DIRECTORY_SEPARATOR.'playwright run-server --host %s --port %d --mode launchServer',
        '127.0.0.1',
        $port,
        'Listening on',
    );

    $server->start();
    $server->stop();

    $listening = true;
    $deadline = microtime(true) + 5;

    while ($listening && microtime(true) < $deadline) {
        $socket = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.2);

        if ($socket === false) {
            $listening = false;
        } else {
            fclose($socket);
            usleep(100_000);
        }
    }

    expect($listening)->toBeFalse();
})->skipOnWindows();
