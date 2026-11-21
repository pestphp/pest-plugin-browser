<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

use Pest\Browser\Exceptions\FrontendUnavailableException;

/**
 * @internal
 */
final readonly class Bridge
{
    /**
     * The path on which the bridge answers with its identity.
     */
    public const string IDENTITY_PATH = '/__pest-bridge';

    /**
     * The seconds the bridge has to start listening.
     */
    private const int START_TIMEOUT = 10;

    /**
     * Creates a new bridge instance.
     *
     * @param  list<string>  $routeHeaders
     */
    public function __construct(
        public int $port,
        public array $routeHeaders,
        public bool $forwardLaneHost,
    ) {
        //
    }

    /**
     * Returns the URL the frontend must use to reach the backend.
     */
    public function url(): string
    {
        return sprintf('http://127.0.0.1:%d', $this->port);
    }

    /**
     * Returns the directory where each process publishes the URL of its HTTP server.
     */
    public function lanesDirectory(): string
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-bridge-'.$this->port;

        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        return $directory;
    }

    /**
     * Publishes the URL of the HTTP server of the given lane.
     */
    public function register(int $lane, string $backendUrl): void
    {
        file_put_contents($this->lanesDirectory().DIRECTORY_SEPARATOR.$lane, mb_rtrim($backendUrl, '/'), LOCK_EX);
    }

    /**
     * Removes the URL of the HTTP server of the given lane.
     */
    public function forget(int $lane): void
    {
        @unlink($this->lanesDirectory().DIRECTORY_SEPARATOR.$lane);
    }

    /**
     * Returns the signature of the configuration the bridge routes with.
     */
    public function signature(): string
    {
        return hash('xxh128', implode('|', [
            implode(',', $this->routeHeaders),
            $this->forwardLaneHost ? '1' : '0',
            $this->lanesDirectory(),
        ]));
    }

    /**
     * Starts the bridge, or reuses the one that already listens on its port.
     *
     * @throws FrontendUnavailableException
     */
    public function ensureRunning(): void
    {
        if (Network::isListening($this->port)) {
            $identity = $this->identity();

            if ($identity === null) {
                throw new FrontendUnavailableException(sprintf(
                    'The port %d of the browser bridge is used by another program. Choose a free port with [->bridgePort()].',
                    $this->port,
                ));
            }

            if ($identity['signature'] === $this->signature()) {
                return;
            }

            if ($this->hasLiveLanes()) {
                throw new FrontendUnavailableException(sprintf(
                    'The browser bridge on port %d routes with another configuration and serves a running test suite. Choose a free port with [->bridgePort()].',
                    $this->port,
                ));
            }

            DetachedProcess::stop($identity['pid']);

            Network::waitUntil(fn (): bool => ! Network::isListening($this->port), self::START_TIMEOUT);
        }

        $this->start();
    }

    /**
     * Returns the identity of the bridge that listens on the port, if it is a bridge.
     *
     * @return array{pid: int, signature: string}|null
     */
    public function identity(): ?array
    {
        $response = Network::get($this->url().self::IDENTITY_PATH, 2.0);

        if ($response === null) {
            return null;
        }

        $identity = json_decode($response['body'], true);

        if (! is_array($identity) || ($identity['bridge'] ?? null) !== 'pest') {
            return null;
        }

        $pid = $identity['pid'] ?? 0;
        $signature = $identity['signature'] ?? '';

        return [
            'pid' => is_int($pid) ? $pid : 0,
            'signature' => is_string($signature) ? $signature : '',
        ];
    }

    /**
     * Returns the path of the bridge script.
     */
    private function script(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'bridge'.DIRECTORY_SEPARATOR.'bridge.mjs';
    }

    /**
     * Starts the bridge process.
     *
     * @throws FrontendUnavailableException
     */
    private function start(): void
    {
        $process = DetachedProcess::start(
            sprintf('node %s', escapeshellarg($this->script())),
            dirname($this->script()),
            [
                'PEST_BRIDGE_PORT' => (string) $this->port,
                'PEST_BRIDGE_LANES' => $this->lanesDirectory(),
                'PEST_BRIDGE_SIGNATURE' => $this->signature(),
                'PEST_BRIDGE_ROUTE_HEADERS' => implode(',', $this->routeHeaders),
                'PEST_BRIDGE_FORWARD_LANE_HOST' => $this->forwardLaneHost ? '1' : '0',
            ],
            sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-bridge-'.$this->port.'.log',
        );

        $started = Network::waitUntil(
            fn (): bool => Network::isListening($this->port, 0.2) || ! $process->running(),
            self::START_TIMEOUT,
        );

        if ($started && $this->identity() !== null) {
            return;
        }

        throw new FrontendUnavailableException(sprintf(
            "The browser bridge did not start on port %d. Ensure [node] is on the PATH.\n%s",
            $this->port,
            $process->tail(),
        ));
    }

    /**
     * Determines whether a running process published its HTTP server in the lanes directory.
     */
    private function hasLiveLanes(): bool
    {
        $entries = scandir($this->lanesDirectory());

        return array_any($entries === false ? [] : $entries, fn (string $entry): bool => preg_match('/^\d+$/', $entry) === 1 && DetachedProcess::isRunning((int) $entry));
    }
}
