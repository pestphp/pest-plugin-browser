<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;
use Pest\Browser\Playwright\Servers\PlaywrightNpmServer;
use Pest\Browser\ServerManager;

beforeEach(function (): void {
    $this->paratest = $_SERVER['PARATEST'] ?? null;
    $this->stateFile = getenv('PEST_BROWSER_STATE_FILE');

    unset($_SERVER['PARATEST']);

    putenv('PEST_BROWSER_STATE_FILE='.sys_get_temp_dir().'/pest-browser-state-'.uniqid().'.json');
});

afterEach(function (): void {
    AlreadyStartedPlaywrightServer::markAsStopped();

    putenv($this->stateFile === false ? 'PEST_BROWSER_STATE_FILE' : 'PEST_BROWSER_STATE_FILE='.$this->stateFile);

    if ($this->paratest !== null) {
        $_SERVER['PARATEST'] = $this->paratest;
    }
});

it('keeps the same playwright server for the whole process', function (): void {
    $manager = new ServerManager();

    $playwright = $manager->playwright();

    expect($playwright)->toBeInstanceOf(PlaywrightNpmServer::class)
        ->and($manager->playwright())->toBe($playwright);
});

it('leaves the state file of a running parallel run to the process that started its server', function (): void {
    AlreadyStartedPlaywrightServer::persist('127.0.0.1', 4001);

    $secondProcess = new ServerManager();

    $secondProcess->playwright()->stop();

    expect(AlreadyStartedPlaywrightServer::fromPersisted()->port)->toBe(4001);
});
