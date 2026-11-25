<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

use Closure;
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
        public string $project,
    ) {
        //
    }

    /**
     * Determines whether a running process, other than the given one, published its HTTP server in the lanes directory.
     */
    public static function hasLiveLanesIn(string $directory, ?int $except = null): bool
    {
        $entries = is_dir($directory) ? scandir($directory) : false;

        return array_any(
            $entries === false ? [] : $entries,
            fn (string $entry): bool => preg_match('/^\d+$/', $entry) === 1 && (int) $entry !== $except && DetachedProcess::isRunning((int) $entry),
        );
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
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-bridge-'.$this->port.'-'.$this->project;

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
            (string) hash_file('xxh128', $this->script()),
        ]));
    }

    /**
     * Starts the bridge, or reuses the one that already listens on its port, one test run at a time.
     *
     * @throws FrontendUnavailableException
     */
    public function ensureRunning(): void
    {
        $this->locked($this->startOrReuse(...));
    }

    /**
     * Starts or reuses the bridge and publishes the URL of the HTTP server of the given lane, so that no
     * other test run replaces the bridge between both steps.
     *
     * @throws FrontendUnavailableException
     */
    public function join(int $lane, string $backendUrl): void
    {
        $this->locked(function () use ($lane, $backendUrl): void {
            $this->startOrReuse();

            $this->register($lane, $backendUrl);
        });
    }

    /**
     * Returns the identity of the bridge that listens on the port, if it is a bridge.
     *
     * @return array{pid: int, signature: string, lanes: string}|null
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
        $lanes = $identity['lanes'] ?? '';

        return [
            'pid' => is_int($pid) ? $pid : 0,
            'signature' => is_string($signature) ? $signature : '',
            'lanes' => is_string($lanes) ? $lanes : '',
        ];
    }

    /**
     * Runs the callback while this process holds the lock of the bridge port.
     *
     * @param  Closure(): void  $callback
     *
     * @throws FrontendUnavailableException
     */
    private function locked(Closure $callback): void
    {
        $lock = fopen(sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-bridge-'.$this->port.'.lock', 'ce');

        try {
            if ($lock !== false) {
                flock($lock, LOCK_EX);
            }

            $callback();
        } finally {
            if ($lock !== false) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    /**
     * Starts the bridge, or reuses the one that already listens on its port.
     *
     * @throws FrontendUnavailableException
     */
    private function startOrReuse(): void
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

            if (self::hasLiveLanesIn($identity['lanes'] === '' ? $this->lanesDirectory() : $identity['lanes'])) {
                throw $this->busyWithAnotherConfiguration();
            }

            DetachedProcess::stop($identity['pid']);

            Network::waitUntil(fn (): bool => ! Network::isListening($this->port), self::START_TIMEOUT);
        }

        $this->start();
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

        $identity = $started ? $this->identity() : null;

        if ($identity !== null && $identity['signature'] === $this->signature()) {
            return;
        }

        $this->stopFailedStart($process);

        if ($identity !== null) {
            throw $this->busyWithAnotherConfiguration();
        }

        throw new FrontendUnavailableException(sprintf(
            "The browser bridge did not start on port %d. Ensure [node] is on the PATH.\n%s",
            $this->port,
            $process->tail(),
        ));
    }

    /**
     * Stops the bridge process a failed start launched, so that it does not open the port after the test gave up.
     */
    private function stopFailedStart(DetachedProcess $process): void
    {
        DetachedProcess::stop($process->pid);
    }

    /**
     * Returns the reason a bridge with another configuration cannot serve this test run.
     */
    private function busyWithAnotherConfiguration(): FrontendUnavailableException
    {
        return new FrontendUnavailableException(sprintf(
            'The browser bridge on port %d routes with another configuration and serves a running test suite. Choose a free port with [->bridgePort()].',
            $this->port,
        ));
    }
}
