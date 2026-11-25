<?php

declare(strict_types=1);

use Pest\Browser\Frontend\DetachedProcess;
use Pest\Browser\Frontend\Network;

it('does not count a process that ended and waits to be collected as running', function (): void {
    $process = proc_open(['/bin/sh', '-c', 'read line'], [0 => ['pipe', 'r']], $pipes);

    expect($process)->not->toBeFalse();

    $pid = proc_get_status($process)['pid'];

    expect(DetachedProcess::isRunning($pid))->toBeTrue();

    fclose($pipes[0]);

    expect(Network::waitUntil(static fn (): bool => ! DetachedProcess::isRunning($pid), 2))->toBeTrue()
        ->and(posix_kill($pid, 0))->toBeTrue();

    proc_close($process);
});
