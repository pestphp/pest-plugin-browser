<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

use Pest\Browser\Exceptions\FrontendUnavailableException;

/**
 * @internal
 */
final readonly class DetachedProcess
{
    /**
     * The seconds a stopped process has to end before it is killed.
     */
    private const float KILL_AFTER = 2.0;

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
        // The process outlives the test run, so it must not keep the sockets of this process open, such as the port of
        // its HTTP server or a connection to the test database: the launching shell closes every descriptor above the
        // standard ones before it starts the command. Bash closes any of them; a plain POSIX shell can only name the
        // ones up to 9, so the ones above point at /dev/null instead, when the limit of open files allows it.
        $bash = is_executable('/bin/bash');
        $closeDescriptors = sprintf(
            'for fd in /proc/self/fd/* /dev/fd/*; do fd=${fd##*/}; if [ "$fd" -gt 2 ]%s; then eval "exec $fd>&-"; fi; done; ',
            $bash ? '' : ' && [ "$fd" -le 9 ]',
        );

        $shell = $closeDescriptors.sprintf('nohup /bin/sh -c %s > %s 2>&1 < /dev/null & echo $!', escapeshellarg($command), escapeshellarg($log));
        $arguments = [$bash ? '/bin/bash' : '/bin/sh', '-c', $shell];
        $environment = array_merge(self::inheritedEnvironment(), $environment);
        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = false;

        if (! $bash) {
            $neutralized = $descriptors;

            foreach (self::openDescriptors() as $descriptor) {
                if ($descriptor > 9) {
                    $neutralized[$descriptor] = ['file', '/dev/null', 'r'];
                }
            }

            $process = @proc_open($arguments, $neutralized, $pipes, $directory, $environment);
        }

        if ($process === false) {
            $process = @proc_open($arguments, $descriptors, $pipes, $directory, $environment);
        }

        if ($process === false) {
            throw new FrontendUnavailableException(sprintf('Unable to run [%s]: %s', $command, error_get_last()['message'] ?? 'proc_open failed.'));
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
        if ($pid <= 0) {
            return false;
        }

        if (function_exists('posix_kill')) {
            $exists = posix_kill($pid, 0) || posix_get_last_error() === 1;
        } else {
            exec(sprintf('kill -0 %d 2>/dev/null', $pid), $output, $code);

            $exists = $code === 0;
        }

        return $exists && ! self::isZombie($pid);
    }

    /**
     * Stops the process with the given id and every process it started, killing the ones that outlive the request.
     */
    public static function stop(int $pid): void
    {
        if ($pid <= 0) {
            return;
        }

        $tree = [$pid, ...self::descendantsOf($pid)];

        self::signal('', $tree);

        $stopped = Network::waitUntil(
            static fn (): bool => array_all($tree, static fn (int $member): bool => ! self::isRunning($member)),
            self::KILL_AFTER,
        );

        if ($stopped) {
            return;
        }

        $survivors = array_filter($tree, self::isRunning(...));

        self::signal('-9 ', array_values(array_unique([...$survivors, ...self::descendantsOf($pid)])));
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
     * Returns the descriptors above the standard ones that this process has open.
     *
     * @return list<int>
     */
    private static function openDescriptors(): array
    {
        $entries = @scandir(is_dir('/proc/self/fd') ? '/proc/self/fd' : '/dev/fd');

        return array_values(array_filter(
            array_map(intval(...), array_filter($entries === false ? [] : $entries, ctype_digit(...))),
            static fn (int $descriptor): bool => $descriptor > 2,
        ));
    }

    /**
     * Determines whether the process with the given id ended and only waits for its parent to collect it, reading
     * its state from the proc file system on Linux and from ps elsewhere; an unknown state is not a zombie.
     */
    private static function isZombie(int $pid): bool
    {
        $stat = @file_get_contents(sprintf('/proc/%d/stat', $pid));

        $end = is_string($stat) ? mb_strrpos($stat, ')') : false;

        if (is_string($stat) && $end !== false) {
            return mb_substr(mb_ltrim(mb_substr($stat, $end + 1)), 0, 1) === 'Z';
        }

        exec(sprintf('ps -o stat= -p %d 2>/dev/null', $pid), $output, $code);

        return $code === 0 && str_starts_with(mb_trim(implode('', $output)), 'Z');
    }

    /**
     * Sends the given signal option to the given processes.
     *
     * @param  list<int>  $pids
     */
    private static function signal(string $option, array $pids): void
    {
        if ($pids !== []) {
            exec(sprintf('kill %s%s 2>/dev/null', $option, implode(' ', $pids)));
        }
    }

    /**
     * Returns the ids of the processes the given process started, and of the ones they started.
     *
     * @return list<int>
     */
    private static function descendantsOf(int $pid): array
    {
        exec(sprintf('pgrep -P %d 2>/dev/null', $pid), $output);

        $descendants = [];

        foreach ($output as $line) {
            $child = (int) mb_trim($line);

            if ($child > 0) {
                $descendants = [...$descendants, $child, ...self::descendantsOf($child)];
            }
        }

        return $descendants;
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
