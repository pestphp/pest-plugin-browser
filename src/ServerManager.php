<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Exceptions\ServerNotFoundException;
use Pest\Browser\Support\FakeProcess;
use Pest\Browser\Support\Process;
use Pest\Plugins\Parallel;

/**
 * @internal
 */
final class ServerManager
{
    /**
     * The default host for the server.
     */
    private const string DEFAULT_HOST = '127.0.0.1';

    /**
     * The singleton instance of the server manager.
     */
    private static ?ServerManager $instance = null;

    /**
     * The HTTP server process.
     */
    private ?Process $http = null;

    /**
     * The Playwright server process.
     */
    private ?Process $playwright = null;

    /**
     * Gets the singleton instance of the server manager.
     */
    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Returns the "http" instance based on the environment.
     *
     * @throws ServerNotFoundException
     */
    public function http(): Process
    {
        if ($this->http instanceof Process) {
            return $this->http;
        }

        $baseDirectory = match (true) {
            // laravel...
            function_exists('app_path') => app_path(),

            // playground...
            file_exists(__DIR__.'/../playground/artisan') => __DIR__.'/../playground',

            // no server found...
            default => throw new ServerNotFoundException('No server found for the current environment.'),
        };

        assert(is_string($baseDirectory));

        return $this->http = Process::create(
            $baseDirectory,
            'php artisan serve --host=%s --port=%d',
            self::DEFAULT_HOST,
            'Server running on',
        );
    }

    /**
     * Returns the Playwright server process instance.
     */
    public function playwright(): Process|FakeProcess
    {
        if (Parallel::isWorker()) {
            return new FakeProcess(
                self::DEFAULT_HOST,
                8077,
            );
        }

        return $this->playwright ??= Process::create(
            __DIR__.'/..',
            'npx playwright run-server --host %s --port %d',
            self::DEFAULT_HOST,
            'Listening on',
            8077,
        );
    }
}
