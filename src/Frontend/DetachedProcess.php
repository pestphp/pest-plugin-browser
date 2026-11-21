<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

use RuntimeException;

/**
 * @internal
 */
final readonly class DetachedProcess
{
    /**
     * Creates a new detached process instance.
     */
    private function __construct(
        public int $pid,
        public string $log,
    ) {
        //
    }

    /**
     * Starts the command in the background, so that it outlives the test run.
     *
     * @param  array<string, string>  $environment
     */
    public static function start(string $command, string $directory, array $environment, string $log): self
    {
        $shell = PHP_OS_FAMILY === 'Windows'
            ? sprintf('start "" /B cmd /C "%s > %s 2>&1"', $command, $log)
            : sprintf('nohup /bin/sh -c %s > %s 2>&1 < /dev/null & echo $!', escapeshellarg($command), escapeshellarg($log));

        $process = proc_open(
            PHP_OS_FAMILY === 'Windows' ? $shell : ['/bin/sh', '-c', $shell],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $directory,
            array_merge(self::inheritedEnvironment(), $environment),
        );

        if ($process === false) {
            throw new RuntimeException(sprintf('Unable to run [%s].', $command));
        }

        $output = (string) stream_get_contents($pipes[1]);

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return new self((int) mb_trim($output), $log);
    }

    /**
     * Determines whether the process with the given id still runs.
     */
    public static function isRunning(int $pid): bool
    {
        if ($pid <= 0 || PHP_OS_FAMILY === 'Windows') {
            return true;
        }

        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0) || posix_get_last_error() === 1;
        }

        exec(sprintf('kill -0 %d 2>/dev/null', $pid), $output, $code);

        return $code === 0;
    }

    /**
     * Stops the process with the given id.
     */
    public static function stop(int $pid): void
    {
        if ($pid <= 0) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            exec(sprintf('taskkill /F /T /PID %d', $pid));

            return;
        }

        exec(sprintf('kill %d 2>/dev/null', $pid));
    }

    /**
     * Returns the last lines of the log of the process.
     */
    public function tail(int $lines = 20): string
    {
        $content = @file_get_contents($this->log);

        if ($content === false) {
            return '';
        }

        return implode(PHP_EOL, array_slice(explode(PHP_EOL, mb_rtrim($content)), -$lines));
    }

    /**
     * Determines whether the process still runs.
     */
    public function running(): bool
    {
        return self::isRunning($this->pid);
    }

    /**
     * Returns the environment of the current process.
     *
     * @return array<string, string>
     */
    private static function inheritedEnvironment(): array
    {
        return array_filter(getenv(), is_string(...));
    }
}
