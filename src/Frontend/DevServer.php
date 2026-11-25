<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

use Pest\Browser\Exceptions\FrontendUnavailableException;

/**
 * @internal
 */
final readonly class DevServer
{
    /**
     * The seconds one readiness request may take, as a dev server compiles a page on its first request.
     */
    private const float REQUEST_TIMEOUT = 60.0;

    /**
     * The seconds a stopped dev server has to free its port.
     */
    private const int STOP_TIMEOUT = 10;

    /**
     * The prefixes of the environment variables that frontend tools expose to the browser at build time.
     *
     * @var list<string>
     */
    private const array PUBLIC_PREFIXES = ['VITE_', 'NEXT_PUBLIC_', 'NUXT_PUBLIC_', 'REACT_APP_', 'PUBLIC_', 'EXPO_PUBLIC_'];

    /**
     * Creates a new dev server instance.
     *
     * @param  array<string, string>  $environment
     */
    public function __construct(
        public string $directory,
        public string $command,
        public string $host,
        public int $port,
        public array $environment,
        public string $readyPath,
        public int $timeout,
        public ?string $bridgePrefix,
    ) {
        //
    }

    /**
     * Returns the base URL of the dev server.
     */
    public function url(): string
    {
        return sprintf('http://%s:%d', Network::urlHost($this->host), $this->port);
    }

    /**
     * Returns the command with its placeholders replaced.
     */
    public function resolvedCommand(): string
    {
        return strtr($this->command, [
            '{port}' => (string) $this->port,
            '{host}' => $this->host,
        ]);
    }

    /**
     * Starts the dev server, or reuses the one that already listens on its port.
     *
     * @throws FrontendUnavailableException
     */
    public function ensureRunning(Bridge $bridge, string $laneHost): void
    {
        if (! Network::isListening($this->port, hosts: $this->listeningHosts())) {
            $this->start($bridge, $laneHost);
        } else {
            $this->reuse($bridge, $laneHost);
        }

        $this->ensureForwardsTo($bridge, $laneHost);
    }

    /**
     * Returns the addresses on which the dev server listens for its host.
     *
     * @return list<string>
     */
    public function listeningHosts(): array
    {
        return $this->host === 'localhost' ? ['127.0.0.1', '::1'] : [mb_trim($this->host, '[]')];
    }

    /**
     * Returns the signature of the command, the directory and the environment the dev server runs with.
     */
    public function signature(): string
    {
        $environment = array_merge($this->publicEnvironment(), $this->environment);

        ksort($environment);

        return hash('xxh128', serialize([$this->resolvedCommand(), $this->directory, $environment]));
    }

    /**
     * Returns the file in which the plugin records the dev server it started on the port.
     */
    public function stateFile(): string
    {
        return $this->file('json');
    }

    /**
     * Returns the file with the given extension that belongs to the dev server on its host and port.
     */
    public function file(string $extension): string
    {
        return sprintf(
            '%s%spest-browser-frontend-%s-%d.%s',
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            preg_replace('/[^A-Za-z0-9.-]/', '_', $this->host),
            $this->port,
            $extension,
        );
    }

    /**
     * Returns the inherited variables a frontend tool reads at build time, by their public prefixes, sorted by name.
     *
     * @return array<string, string>
     */
    private function publicEnvironment(): array
    {
        $public = array_filter(
            array_filter(getenv(), is_string(...)),
            fn (string $name): bool => array_any(self::PUBLIC_PREFIXES, fn (string $prefix): bool => str_starts_with($name, $prefix)),
            ARRAY_FILTER_USE_KEY,
        );

        ksort($public);

        return $public;
    }

    /**
     * Reuses the dev server that listens on the port, or replaces it when the plugin started it with another
     * configuration or it does not answer, unless it serves another running test suite.
     *
     * @throws FrontendUnavailableException
     */
    private function reuse(Bridge $bridge, string $laneHost): void
    {
        $owned = $this->ownedProcess();

        if ($owned === null) {
            return;
        }

        $sameConfiguration = $owned['signature'] === $this->signature();

        if ($sameConfiguration && Network::waitUntil(fn (float $remaining): bool => $this->answers($laneHost, $remaining), $this->timeout)) {
            return;
        }

        if (Bridge::hasLiveLanesIn($owned['lanes'], Frontend::lane())) {
            throw new FrontendUnavailableException($sameConfiguration
                ? sprintf('The frontend on %s did not answer within %d seconds, and other test processes use it.', $this->url().$this->readyPath, $this->timeout)
                : sprintf('The frontend on port %d serves another running test suite. Choose another port.', $this->port));
        }

        $this->stop($owned['pid']);

        $this->start($bridge, $laneHost);
    }

    /**
     * Starts the dev server process and waits until it answers.
     *
     * @throws FrontendUnavailableException
     */
    private function start(Bridge $bridge, string $laneHost): void
    {
        if (! is_dir($this->directory)) {
            throw new FrontendUnavailableException(sprintf(
                'The frontend directory [%s] does not exist.',
                $this->directory,
            ));
        }

        $process = DetachedProcess::start(
            $this->resolvedCommand(),
            $this->directory,
            $this->environment,
            $this->file('log'),
        );

        file_put_contents($this->stateFile(), (string) json_encode([
            'pid' => $process->pid,
            'signature' => $this->signature(),
            'lanes' => $bridge->lanesDirectory(),
        ]), LOCK_EX);

        $ready = Network::waitUntil(
            fn (float $remaining): bool => ! $process->running() || $this->answers($laneHost, $remaining),
            $this->timeout,
        );

        if ($ready && $process->running()) {
            return;
        }

        $this->stop($process->pid);

        throw new FrontendUnavailableException(sprintf(
            "The frontend did not answer on %s within %d seconds after [%s] in [%s]. Log: %s\n%s",
            $this->url().$this->readyPath,
            $this->timeout,
            $this->resolvedCommand(),
            $this->directory,
            $process->log,
            $process->tail(),
        ));
    }

    /**
     * Stops the dev server process the plugin started and forgets it.
     */
    private function stop(int $pid): void
    {
        DetachedProcess::stop($pid);

        @unlink($this->stateFile());

        Network::waitUntil(fn (): bool => ! Network::isListening($this->port, 0.2, $this->listeningHosts()), self::STOP_TIMEOUT);
    }

    /**
     * Returns the dev server the plugin started on the port, if it still runs.
     *
     * @return array{pid: int, signature: string, lanes: string}|null
     */
    private function ownedProcess(): ?array
    {
        $content = @file_get_contents($this->stateFile());
        $state = $content === false ? null : json_decode($content, true);

        if (! is_array($state) || ! is_int($state['pid'] ?? null) || ! DetachedProcess::isRunning($state['pid'])) {
            return null;
        }

        return [
            'pid' => $state['pid'],
            'signature' => is_string($state['signature'] ?? null) ? $state['signature'] : '',
            'lanes' => is_string($state['lanes'] ?? null) ? $state['lanes'] : '',
        ];
    }

    /**
     * Determines whether the dev server answers its ready path without a server error, as the browser of the lane asks it.
     */
    private function answers(string $laneHost, float $remaining): bool
    {
        if (! Network::isListening($this->port, 0.2, $this->listeningHosts())) {
            return false;
        }

        $status = Network::status($this->url().$this->readyPath, min(self::REQUEST_TIMEOUT, $remaining), ['host' => $laneHost]);

        return $status !== null && $status > 0 && $status < 500;
    }

    /**
     * Ensures the dev server forwards the backend requests to this bridge, when a prefix to verify it is configured.
     *
     * @throws FrontendUnavailableException
     */
    private function ensureForwardsTo(Bridge $bridge, string $laneHost): void
    {
        if ($this->bridgePrefix === null) {
            return;
        }

        $url = $this->url().mb_rtrim($this->bridgePrefix, '/').Bridge::IDENTITY_PATH;
        $response = Network::get($url, min(self::REQUEST_TIMEOUT, (float) $this->timeout), ['host' => $laneHost]);
        $identity = $response === null ? null : json_decode($response['body'], true);

        if (is_array($identity) && ($identity['bridge'] ?? null) === 'pest' && ($identity['port'] ?? null) === $bridge->port) {
            return;
        }

        throw new FrontendUnavailableException(sprintf(
            'The frontend on %s does not forward [%s] to the browser bridge on port %d. Another program owns port %d, or the frontend started without its backend URL pointing at %s.',
            $this->url(),
            $url,
            $bridge->port,
            $this->port,
            $bridge->url(),
        ));
    }
}
