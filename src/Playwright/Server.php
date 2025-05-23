<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class Server
{
    private const string DEFAULT_HOST = '127.0.0.1';

    private const int DEFAULT_PORT = 9222;

    /**
     * Playwright server process.
     */
    private Process $process;

    /**
     * Server instance.
     */
    private static ?Server $instance = null;

    /**
     * Constructs new server instance.
     */
    public function __construct(
        private readonly string $host,
        private readonly int $port
    ) {
        //
    }

    /**
     * Returns the server instance.
     */
    public static function instance(): self
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self(
                (string) ($_ENV['PEST_BROWSER_HOST'] ?? self::DEFAULT_HOST), // @phpstan-ignore-line
                (int) ($_ENV['PEST_BROWSER_PORT'] ?? self::DEFAULT_PORT), // @phpstan-ignore-line
            );
        }

        return self::$instance;
    }

    /**
     * Starts the Playwright server.
     */
    public function start(): void
    {
        if ($this->isRunning()) {
            return;
        }

        $this->process = new Process([
            'npx',
            'playwright',
            'run-server',
            '--host',
            $this->host,
            '--port',
            $this->port,
        ]);

        $this->process->start();

        $this->process->waitUntil(
            fn (string $type, string $output): bool => $output === "Listening on {$this->url()}\n"
        );
    }

    /**
     * Checks if the specified port is free for use.
     */
    public function isRunning(): bool
    {
        $process = Process::fromShellCommandline("lsof -t -i :{$this->port} 2>/dev/null");

        $process->run();
        $output = $process->getOutput();

        $pids = array_filter(explode("\n", mb_trim($output)), fn (string $pid): bool => (int) $pid > 0);

        return count($pids) > 0;
    }

    /**
     * Stops the Playwright server.
     */
    public function stop(): void
    {
        if ($this->isRunning()) {
            $command = Process::fromShellCommandline("kill -9 $(lsof -t -i :{$this->port})");
            $command->disableOutput();
            $command->run();
        }
    }

    /**
     * Returns the URL of the Playwright server.
     */
    public function url(string $query = ''): string
    {
        return sprintf('ws://%s:%s/%s', self::DEFAULT_HOST, self::DEFAULT_PORT, $query);
    }
}
