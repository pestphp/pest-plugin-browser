<?php

declare(strict_types=1);

use Pest\Browser\Plugin;
use Pest\Browser\ServerManager;

it('creates a user defined http server for host', function (): void {
    $plugin = new Plugin;
    $plugin->handleArguments(['--http-host=my-server.test']);
    $serverManager = ServerManager::newInstance();
    $httpServer = $serverManager->http();
    expect($httpServer->host)->toBe('my-server.test');
})->skip(Pest\Plugins\Parallel::isWorker());

it('creates a user defined http server for bind address', function (): void {
    $plugin = new Plugin;
    $plugin->handleArguments(['--http-bind=0.0.0.0']);
    $serverManager = ServerManager::newInstance();
    $httpServer = $serverManager->http();
    expect($httpServer->bindAddress)->toBe('0.0.0.0');
})->skip(Pest\Plugins\Parallel::isWorker());

it('creates a playwright server for user-defined port', function (): void {
    $plugin = new Plugin;
    $plugin->handleArguments(['--playwright-port=9999']);
    $serverManager = ServerManager::newInstance();
    $playwrightServer = $serverManager->playwright();
    expect($playwrightServer->port)->toBe(9999)
        ->and($playwrightServer->host)->toBe('127.0.0.1');
})->skip(Pest\Plugins\Parallel::isWorker());

it('creates a playwright server for host and port', function (): void {
    $plugin = new Plugin;
    $plugin->handleArguments(['--playwright-port=9999', '--playwright-host=my-server.test']);
    $serverManager = ServerManager::newInstance();
    $playwrightServer = $serverManager->playwright();
    expect($playwrightServer->port)->toBe(9999)
        ->and($playwrightServer->host)->toBe('my-server.test');
})->skip(Pest\Plugins\Parallel::isWorker());

afterEach(function (): void {
    Pest\Browser\Support\PersistHttpServer::reset();
    Pest\Browser\Support\PersistPlaywrightServer::reset();
});
