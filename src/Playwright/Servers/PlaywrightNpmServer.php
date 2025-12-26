<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright\Servers;

use Pest\Browser\Contracts\PlaywrightServer;
use Pest\Browser\Exceptions\PlaywrightNotInstalledException;
use Pest\Browser\Exceptions\PlaywrightOutdatedException;
use Pest\Browser\Playwright\Playwright;
use RuntimeException;
use Symfony\Component\Process\Process as SystemProcess;

/**
 * @internal
 *
 * @codeCoverageIgnore This class is used at plugin level to manage processes.
 */
final class PlaywrightNpmServer implements PlaywrightServer
{
    /**
     * The playwright version required to run this server.
     */
    private const string PLAYWRIGHT_VERSION = '1.54.1';

    /**
     * The underlying process instance, if any.
     */
    private ?SystemProcess $systemProcess = null;

    /**
     * Creates a new playwright npm server instance.
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
     * Creates a new playwright npm server instance with the given parameters.
     */
    public static function create(string $baseDirectory, string $command, string $host, int $port, string $until): self
    {
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

        $this->systemProcess = new SystemProcess(explode(' ', sprintf(
            $this->command,
            $this->host,
            $this->port,
        )), $this->baseDirectory, [
            'APP_URL' => sprintf('http://%s:%d', $this->host, $this->port),
        ]);

        $this->systemProcess->setTimeout(0);

        $this->systemProcess->start();

        $this->systemProcess->waitUntil(
            fn (string $type, string $output): bool => str_contains($output, $this->until)
        );

        if ($this->isRunning() === false) {
            self::ensurePlaywrightIsInstalledAndVersionIsSupported();

            throw new PlaywrightNotInstalledException();
        }

        AlreadyStartedPlaywrightServer::persist(
            $this->host,
            $this->port,
        );
    }

    /**
     * Stops the process if it is running.
     */
    public function stop(): void
    {
        if ($this->systemProcess instanceof SystemProcess && $this->isRunning()) {
            $this->systemProcess->stop(
                timeout: 0.1,
                signal: PHP_OS_FAMILY === 'Windows' ? null : SIGTERM,
            );
        }

        $this->systemProcess = null;

        AlreadyStartedPlaywrightServer::markAsStopped();
    }

    /**
     * Flushes the process.
     */
    public function flush(): void
    {
        //
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
     * Ensures that Playwright is installed and the version is supported.
     *
     * @throws PlaywrightNotInstalledException
     */
    private function ensurePlaywrightIsInstalledAndVersionIsSupported(): void
    {
        $process = SystemProcess::fromShellCommandline(
            '.'.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'.bin'.DIRECTORY_SEPARATOR.'playwright run-server --version',
            $this->baseDirectory,
        );

        $process->run();

        if (! $process->isSuccessful()) {
            throw new PlaywrightNotInstalledException();
        }

        $output = $process->getOutput();

        // check if the output matches the required version
        if (in_array(preg_match('/^Version\s+(\d+\.\d+\.\d+)/', $output, $matches), [0, false], true)) {
            throw new PlaywrightNotInstalledException();
        }

        $version = $matches[1];

        if (version_compare($version, self::PLAYWRIGHT_VERSION, '<')) {
            throw new PlaywrightOutdatedException();
        }
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
