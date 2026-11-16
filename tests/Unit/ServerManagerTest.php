<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;
use Pest\Browser\Playwright\Servers\PlaywrightNpmServer;
use Pest\Browser\ServerManager;

beforeEach(function (): void {
    $this->paratest = $_SERVER['PARATEST'] ?? null;

    unset($_SERVER['PARATEST']);

    putenv('PEST_BROWSER_STATE_FILE='.sys_get_temp_dir().'/pest-browser-state-'.uniqid().'.json');
});

afterEach(function (): void {
    AlreadyStartedPlaywrightServer::markAsStopped();

    putenv('PEST_BROWSER_STATE_FILE');

    if ($this->paratest !== null) {
        $_SERVER['PARATEST'] = $this->paratest;
    }
});

it('persists the port of the playwright server it keeps', function (): void {
    $manager = new ServerManager();

    $playwright = $manager->playwright();

    expect($playwright)->toBeInstanceOf(PlaywrightNpmServer::class)
        ->and($manager->playwright())->toBe($playwright)
        ->and(AlreadyStartedPlaywrightServer::fromPersisted()->port)->toBe($playwright->port);
});
