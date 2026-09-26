<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Servers\AlreadyStartedPlaywrightServer;

/*
 * The state file used to be one path per project, so a second test run in the
 * same checkout overwrote it and then deleted it on its way out. These cover the
 * two halves of that: the description has to survive a foreign teardown, and a
 * worker has to resolve the run it belongs to.
 */

afterEach(function (): void {
    putenv('PEST_BROWSER_RUN_ID');
    unset($_SERVER['PEST_BROWSER_RUN_ID'], $_ENV['PEST_BROWSER_RUN_ID']);
});

/** Runs a closure under a given run id, the way a worker inherits one. */
function underRunId(string $runId, Closure $work): mixed
{
    putenv('PEST_BROWSER_RUN_ID='.$runId);
    $_SERVER['PEST_BROWSER_RUN_ID'] = $runId;
    $_ENV['PEST_BROWSER_RUN_ID'] = $runId;

    try {
        return $work();
    } finally {
        putenv('PEST_BROWSER_RUN_ID');
        unset($_SERVER['PEST_BROWSER_RUN_ID'], $_ENV['PEST_BROWSER_RUN_ID']);
    }
}

it('keeps one run\'s server when another run tears its own down', function (): void {
    underRunId('run-a', function (): void {
        AlreadyStartedPlaywrightServer::persist('127.0.0.1', 1111);
    });

    underRunId('run-b', function (): void {
        AlreadyStartedPlaywrightServer::persist('127.0.0.1', 2222);
        AlreadyStartedPlaywrightServer::markAsStopped();
    });

    $server = underRunId('run-a', fn (): AlreadyStartedPlaywrightServer => AlreadyStartedPlaywrightServer::fromPersisted());

    expect($server->port)->toBe(1111)
        ->and($server->host)->toBe('127.0.0.1');

    underRunId('run-a', function (): void {
        AlreadyStartedPlaywrightServer::markAsStopped();
    });
});

it('resolves the server belonging to the run it was given', function (): void {
    underRunId('run-a', fn () => AlreadyStartedPlaywrightServer::persist('127.0.0.1', 1111));
    underRunId('run-b', fn () => AlreadyStartedPlaywrightServer::persist('127.0.0.1', 2222));

    $a = underRunId('run-a', fn (): AlreadyStartedPlaywrightServer => AlreadyStartedPlaywrightServer::fromPersisted());
    $b = underRunId('run-b', fn (): AlreadyStartedPlaywrightServer => AlreadyStartedPlaywrightServer::fromPersisted());

    expect($a->port)->toBe(1111)
        ->and($b->port)->toBe(2222);

    underRunId('run-a', fn () => AlreadyStartedPlaywrightServer::markAsStopped());
    underRunId('run-b', fn () => AlreadyStartedPlaywrightServer::markAsStopped());
});

it('exports the run id it mints, so a worker inherits it', function (): void {
    AlreadyStartedPlaywrightServer::persist('127.0.0.1', 3333);

    // putenv() alone is not enough here. Symfony's Process builds the inherited
    // environment as array_intersect_key(getenv(), $_SERVER), so a variable that
    // exists only in getenv() is dropped before a worker ever sees it.
    expect(getenv('PEST_BROWSER_RUN_ID'))->not->toBeFalse()
        ->and($_SERVER)->toHaveKey('PEST_BROWSER_RUN_ID')
        ->and($_ENV)->toHaveKey('PEST_BROWSER_RUN_ID');

    AlreadyStartedPlaywrightServer::markAsStopped();
});
