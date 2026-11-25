<?php

declare(strict_types=1);

use Pest\Browser\Frontend\DetachedProcess;

it('starts a detached process near the limit of open files', function (): void {
    $limits = posix_getrlimit();
    $hard = is_numeric($limits['hard openfiles']) ? (int) $limits['hard openfiles'] : PHP_INT_MAX;
    $soft = is_numeric($limits['soft openfiles']) ? (int) $limits['soft openfiles'] : PHP_INT_MAX;
    $open = count(array_filter((array) scandir(is_dir('/proc/self/fd') ? '/proc/self/fd' : '/dev/fd'), ctype_digit(...)));
    $files = [];

    for ($i = 0; $i < 30; $i++) {
        $files[] = fopen('/dev/null', 'r');
    }

    expect(posix_setrlimit(POSIX_RLIMIT_NOFILE, $open + 40, $hard))->toBeTrue();

    try {
        $process = DetachedProcess::start('true', sys_get_temp_dir(), [], '/dev/null');

        expect($process->pid)->toBeGreaterThan(0);
    } finally {
        posix_setrlimit(POSIX_RLIMIT_NOFILE, $soft, $hard);

        array_map(fclose(...), $files);
    }
});
