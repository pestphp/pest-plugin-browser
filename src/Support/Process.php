<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use RuntimeException;
use Symfony\Component\Process\Process as SystemProcess;

/**
 * @internal
 */
final class Process
{
    /**
     * The underlying process instance, if any.
     */
    private ?SystemProcess $systemProcess = null;

    /**
     * Creates a new process instance.
     */
    private function __construct(
        public readonly string $baseDirectory,
        public readonly string $command,
        public readonly string $host,
        public readonly int $port,
        public readonly string $until,
    ) {
        //
    }

    /**
     * Creates a new process instance with the given parameters.
     */
    public static function create(string $baseDirectory, string $command, string $host, string $until, ?int $port = null): self
    {
        $startPort = 8010;

        if (getenv('TEST_TOKEN') !== false) {
            $startPort = (((int) getenv('TEST_TOKEN')) * 10) + $startPort;
        }

        $port = $port !== null && $port !== 0 ? $port : Port::findAvailable($host, $startPort, 9000);

        return new self(
            $baseDirectory, $command, $host, $port, $until
        );
    }

    /**
     * Starts the process until the given "output" condition is met.
     */
    public function start(): void
    {
        if ($this->isRunning()) {
            return;
        }

        $this->systemProcess = SystemProcess::fromShellCommandline(sprintf(
            $this->command,
            $this->host,
            $this->port,
        ), $this->baseDirectory);

        $this->systemProcess->start();

        $this->systemProcess->waitUntil(
            fn (string $type, string $output): bool => str_contains($output, $this->until),
        );
    }

    /**
     * Stops the process if it is running.
     */
    public function stop(): void
    {
        if ($this->systemProcess instanceof SystemProcess && $this->isRunning()) {
            $this->systemProcess->stop(
                5,
                defined('SIGTERM') ? SIGTERM : null,
            );
        }

        $this->systemProcess = null;
    }

    /**
     * Returns the URL of the process.
     *
     * @throws RuntimeException If the process has not been started yet or has stopped unexpectedly.
     */
    public function url(): string
    {
        if (! $this->isRunning()) {
            throw new RuntimeException(
                sprintf('The process with arguments [%s] is not running or has stopped unexpectedly.', json_encode([
                    'baseDirectory' => $this->baseDirectory,
                    'command' => $this->command,
                    'host' => $this->host,
                    'port' => $this->port,
                    'until' => $this->until,
                ]),
                ));
        }

        return sprintf('%s:%d', $this->host, $this->port);
    }

    /**
     * Checks
     */
    private function isRunning(): bool
    {
        return $this->systemProcess instanceof SystemProcess
            && $this->systemProcess->isRunning();
    }
}
