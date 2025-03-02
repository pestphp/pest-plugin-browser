<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class Server
{
    private const DEFAULT_HOST = 'localhost';

    private const DEFAULT_PORT = 9222;

    /**
     * Playwright server process.
     */
    private readonly Process $process;

    /**
     * Server instance.
     */
    private static ?Server $instance = null;

    /**
     * Starts the Playwright server.
     */
    public function __construct(
        private readonly string $host,
        private readonly int $port
    ) {
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
            fn ($type, $output): bool => $output === "Listening on {$this->url()}\n"
        );
    }

    /**
     * Destructs the server and stops the Playwright server.
     */
    public function __destruct()
    {
        $this->stop();
    }

    /**
     * Creates new server instance and starts the Playwright server.
     */
    public static function start(): self
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self(
                $_ENV['PEST_BROWSER_PLUGIN_PLAYWRIGHT_HOST'] ?? self::DEFAULT_HOST,
                $_ENV['PEST_BROWSER_PLUGIN_PLAYWRIGHT_PORT'] ?? self::DEFAULT_PORT,
            );
        }

        return self::$instance;
    }

    /**
     * Stops the Playwright server.
     */
    public function stop(): void
    {
        // Stop this way because initial process changes PID for some reason
        $command = "kill -15 $(lsof -t -i :{$this->port}) 2>/dev/null";
        Process::fromShellCommandline($command)->run();
    }

    /**
     * Returns the URL of the Playwright server.
     */
    public function url(): string
    {
        return sprintf('ws://%s:%s/', self::DEFAULT_HOST, self::DEFAULT_PORT);
    }
}
