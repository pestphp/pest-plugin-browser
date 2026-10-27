<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;

afterEach(function (): void {
    putenv('PEST_BROWSER_STATE_FILE');
});

it('keeps the state of runs with different state files apart', function (): void {
    $first = sys_get_temp_dir().'/pest-browser-state-'.uniqid().'.json';
    $second = sys_get_temp_dir().'/pest-browser-state-'.uniqid().'.json';

    putenv("PEST_BROWSER_STATE_FILE={$first}");
    AlreadyStartedPlaywrightServer::persist('127.0.0.1', 4001);

    putenv("PEST_BROWSER_STATE_FILE={$second}");
    AlreadyStartedPlaywrightServer::persist('127.0.0.1', 4002);
    AlreadyStartedPlaywrightServer::markAsStopped();

    putenv("PEST_BROWSER_STATE_FILE={$first}");
    $restored = AlreadyStartedPlaywrightServer::fromPersisted();
    AlreadyStartedPlaywrightServer::markAsStopped();

    expect($restored->port)->toBe(4001)
        ->and(file_exists($second))->toBeFalse()
        ->and(file_exists($first))->toBeFalse();
});
