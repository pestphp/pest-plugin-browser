<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Playwright;

it('keeps the connection when a forked child exits', function (): void {
    Route::get('/', fn (): string => '<div>Welcome</div>');

    // PHP reuses freed object handles, last freed first, and destroys objects in handle order at shutdown.
    // A process that has already created and freed many objects (any long test run) destroys the socket
    // of a new connection before its read and write streams, so the socket closes the stream itself.
    Playwright::close();
    Client::instance()->disconnect();

    $objects = [];

    for ($i = 0; $i < 10_000; $i++) {
        $objects[] = new stdClass();
    }

    unset($objects);

    $this->__markAsBrowserTest();

    visit('/')->assertSee('Welcome');

    $pid = pcntl_fork();

    if ($pid === 0) {
        exit(0);
    }

    pcntl_waitpid($pid, $status);

    visit('/')->assertSee('Welcome');
})->skip(! function_exists('pcntl_fork'), 'Requires the pcntl extension.');
