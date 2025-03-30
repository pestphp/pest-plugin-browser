<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Support\Arr;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class Playground
{
    private const DEFAULT_SCHEME = 'http';

    private const DEFAULT_HOST = 'localhost';

    private const DEFAULT_PORT = 9357;

    /**
     * Playground instance.
     */
    private static ?Playground $instance = null;

    /**
     * Artisan serve process.
     */
    private Process $process;

    /**
     * Constructs new instance and starts Artisan serve process.
     */
    private function __construct(
        private string $scheme,
        private string $host,
        private int $port
    ) {
        $this->shutdownIdleProcesses();

        $this->process = new Process(['php', 'playground/artisan', 'serve', "--port={$this->port}"]);
        $this->process->disableOutput();
        $this->process->start();
    }

    /**
     * Destruct to stop Artisan serve process.
     */
    public function __destruct()
    {
        $this->stop();
    }

    /**
     * Creates new Playground instance or returns existing one.
     */
    public static function start(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(
                Arr::get($_ENV, 'PEST_BROWSER_PLUGIN_PLAYGROUND_SCHEME', self::DEFAULT_SCHEME), // @phpstan-ignore-line
                Arr::get($_ENV, 'PEST_BROWSER_PLUGIN_PLAYGROUND_HOST', self::DEFAULT_HOST), // @phpstan-ignore-line
                Arr::get($_ENV, 'PEST_BROWSER_PLUGIN_PLAYGROUND_PORT', self::DEFAULT_PORT), // @phpstan-ignore-line
            );
        }

        return self::$instance;
    }

    /**
     * Get URL for given path.
     */
    public function url(string $path = '/'): string
    {
        return sprintf(
            '%s://%s:%d/%s',
            $this->scheme,
            $this->host,
            $this->port,
            ltrim($path, '/')
        );
    }

    /**
     * Stops Artisan serve process.
     */
    private function stop(): void
    {
        if ($this->process->isRunning()) {
            $this->process->stop(0, SIGKILL);
        }
    }

    /**
     * Shuts down idle processes listening on the given port.
     */
    private function shutdownIdleProcesses(): void
    {
        $process = new Process(['lsof', '-i', "tcp:$this->port"]);
        $process->run();

        if (! $process->isSuccessful()) {
            return;
        }

        $output = $process->getOutput();
        $lines = explode("\n", trim($output));

        foreach (array_slice($lines, 1) as $line) {
            $columns = preg_split('/\s+/', $line);
            $pid = $columns[1] ?? null;
            $command = $columns[0] ?? '';

            if ($pid !== null && str_contains($command, 'php')) {
                posix_kill((int) $pid, SIGKILL);
            }
        }
    }
}
