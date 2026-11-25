<?php

declare(strict_types=1);

namespace Tests\Support;

use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\DetachedProcess;
use Pest\Browser\Frontend\Network;
use Pest\Browser\Support\Port;
use RuntimeException;
use Symfony\Component\Process\Process;

final class FrontendFixture
{
    /**
     * The fixture backends that run.
     *
     * @var list<Process>
     */
    private static array $backends = [];

    /**
     * Returns the directory of the fixture frontend.
     */
    public static function directory(): string
    {
        return __DIR__.'/../Fixtures/frontend';
    }

    /**
     * Returns the command that serves the fixture frontend.
     */
    public static function command(): string
    {
        return escapeshellarg(PHP_BINARY).' -S 127.0.0.1:{port} router.php';
    }

    /**
     * Starts a backend that answers with the request it received, and returns its URL.
     */
    public static function backend(): string
    {
        $port = Port::find();
        $process = new Process([PHP_BINARY, '-S', '127.0.0.1:'.$port, self::directory().'/backend.php']);
        $process->setTimeout(0);
        $process->start();

        self::$backends[] = $process;

        if (! Network::waitUntil(fn (): bool => Network::isListening($port, 0.2), 5)) {
            throw new RuntimeException('Could not start the fixture backend: '.$process->getErrorOutput());
        }

        return 'http://127.0.0.1:'.$port;
    }

    /**
     * Returns the id of the process that listens on the port of the given URL.
     */
    public static function pidOf(string $url): int
    {
        return (int) shell_exec(sprintf('lsof -ti tcp:%d -sTCP:LISTEN', parse_url($url, PHP_URL_PORT)));
    }

    /**
     * Stops the fixture backends.
     */
    public static function stopBackends(): void
    {
        foreach (self::$backends as $backend) {
            $backend->stop(0.1);
        }

        self::$backends = [];
    }

    /**
     * Stops the bridge that listens on the given port.
     */
    public static function stopBridge(Bridge $bridge): void
    {
        $identity = $bridge->identity();

        if ($identity !== null) {
            DetachedProcess::stop($identity['pid']);
        }

        Network::waitUntil(fn (): bool => ! Network::isListening($bridge->port, 0.2), 5);

        foreach (glob($bridge->lanesDirectory().'/*') ?: [] as $lane) {
            @unlink($lane);
        }

        @rmdir($bridge->lanesDirectory());
        foreach (['log', 'lock'] as $extension) {
            @unlink(sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-bridge-'.$bridge->port.'.'.$extension);
        }
    }

    /**
     * Stops the process that listens on the given port.
     */
    public static function stopPort(int $port): void
    {
        exec(sprintf('lsof -ti tcp:%d -sTCP:LISTEN 2>/dev/null', $port), $pids);

        $pids = array_unique($pids);

        foreach ($pids as $pid) {
            DetachedProcess::stop((int) $pid);
        }

        Network::waitUntil(fn (): bool => ! Network::isListening($port, 0.2, ['127.0.0.1', '::1']), 5);

        foreach (['json', 'log', 'lock'] as $extension) {
            foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-frontend-*-'.$port.'.'.$extension) ?: [] as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * Returns the host the browser of this process uses for the frontend.
     */
    public static function laneHost(): string
    {
        return 'pest-w'.getmypid().'.localhost';
    }
}
