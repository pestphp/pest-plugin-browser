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
        return sprintf('http://%s:%d', $this->host, $this->port);
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
    public function ensureRunning(Bridge $bridge): void
    {
        if (! Network::isListening($this->port)) {
            $this->start();
        }

        $this->ensureForwardsTo($bridge);
    }

    /**
     * Starts the dev server process and waits until it answers.
     *
     * @throws FrontendUnavailableException
     */
    private function start(): void
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
            sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-frontend-'.$this->port.'.log',
        );

        $ready = Network::waitUntil(
            fn (): bool => ! $process->running() || $this->answers(),
            $this->timeout,
        );

        if ($ready && $process->running()) {
            return;
        }

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
     * Determines whether the dev server answers its ready path without a server error.
     */
    private function answers(): bool
    {
        if (! Network::isListening($this->port, 0.2)) {
            return false;
        }

        $response = Network::get($this->url().$this->readyPath, self::REQUEST_TIMEOUT);

        return $response !== null && $response['status'] > 0 && $response['status'] < 500;
    }

    /**
     * Ensures the dev server forwards the backend requests to this bridge, when a prefix to verify it is configured.
     *
     * @throws FrontendUnavailableException
     */
    private function ensureForwardsTo(Bridge $bridge): void
    {
        if ($this->bridgePrefix === null) {
            return;
        }

        $url = $this->url().mb_rtrim($this->bridgePrefix, '/').Bridge::IDENTITY_PATH;
        $response = Network::get($url, self::REQUEST_TIMEOUT);
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
