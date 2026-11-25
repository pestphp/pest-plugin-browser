<?php

declare(strict_types=1);

use Pest\Browser\Frontend\DetachedProcess;

it('does not keep the sockets of the test process open in a detached process', function (): void {
    $server = stream_socket_server('tcp://127.0.0.1:0');
    $port = (int) parse_url('tcp://'.stream_socket_get_name($server, false), PHP_URL_PORT);
    $client = stream_socket_client('tcp://127.0.0.1:'.$port);
    $log = tempnam(sys_get_temp_dir(), 'pest-detached-');

    $process = DetachedProcess::start('sleep 5', sys_get_temp_dir(), [], $log);

    try {
        $sockets = (string) shell_exec(sprintf('lsof -a -p %d -i TCP -n -P 2>/dev/null', $process->pid));

        expect($process->pid)->toBeGreaterThan(0)
            ->and(DetachedProcess::isRunning($process->pid))->toBeTrue()
            ->and($sockets)->not->toContain(':'.$port);
    } finally {
        DetachedProcess::stop($process->pid);
        fclose($client);
        fclose($server);
        @unlink($log);
    }
});
