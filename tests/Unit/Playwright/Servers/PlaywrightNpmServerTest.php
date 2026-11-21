<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;
use Pest\Browser\Playwright\Servers\PlaywrightNpmServer;
use Pest\Browser\Support\PackageJsonDirectory;
use Pest\Browser\Support\Port;

it('stops the node process it started instead of leaving it behind', function (): void {
    $port = Port::find();

    $server = PlaywrightNpmServer::create(
        PackageJsonDirectory::find(),
        '.'.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'.bin'.DIRECTORY_SEPARATOR.'playwright run-server --host %s --port %d --mode launchServer',
        '127.0.0.1',
        $port,
        'Listening on',
    );

    $server->start();

    try {
        $pid = (int) new ReflectionProperty($server, 'systemProcess')->getValue($server)->getPid();
        $commandLine = str_replace("\0", ' ', (string) file_get_contents("/proc/{$pid}/cmdline"));

        expect($commandLine)->toStartWith('node ')
            ->and($commandLine)->toContain("--port {$port}");
    } finally {
        $server->stop();
        AlreadyStartedPlaywrightServer::markAsStopped();
    }

    $survivors = [];

    foreach (glob('/proc/[0-9]*', GLOB_NOSORT) ?: [] as $directory) {
        $candidate = str_replace("\0", ' ', (string) @file_get_contents($directory.'/cmdline'));

        if (str_contains($candidate, "run-server --host 127.0.0.1 --port {$port} ")) {
            $survivors[] = basename($directory);
        }
    }

    expect($survivors)->toBe([]);
})->skipOnWindows()->skipOnMac();
