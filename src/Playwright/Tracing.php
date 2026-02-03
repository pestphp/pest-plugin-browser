<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Enums\TracingOption;
use Pest\TestSuite;

/**
 * @internal
 */
final class Tracing
{
    use Concerns\InteractsWithPlaywright;

    private bool $isTracing = false;

    private bool $isTracingChunk = false;

    /**
     * Creates a new context instance.
     */
    public function __construct(
        private readonly string $guid
    ) {
        //
    }

    /**
     * Return the path to the tracings' directory.
     */
    public static function dir(): string
    {
        return TestSuite::getInstance()->rootPath
            .'/tests/Browser/Tracing';
    }

    /**
     * Return the path for a tracing file.
     */
    public static function path(?string $filename = null): string
    {
        if ($filename === null) {
            // @phpstan-ignore-next-line
            $filename = str_replace('__pest_evaluable_', '', test()->name());
        }

        $path = self::dir().'/'.mb_ltrim($filename, '/');

        // check if there is extension, if not, add .zip
        if (pathinfo($path, PATHINFO_EXTENSION) === '') {
            $path .= '.zip';
        }

        return $path;
    }

    /**
     * Clean up the tracing directory.
     */
    public static function cleanup(): void
    {
        if (is_dir(self::dir()) === false) {
            return;
        }

        $files = glob(self::dir().'/*');

        if (is_array($files)) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }

        @rmdir(self::dir());
    }

    /**
     * Start tracing
     *
     * @param  array<string, mixed>  $params
     */
    public function start(array $params = []): void
    {
        if ($this->isTracing) {
            return;
        }
        $response = Client::instance()->execute($this->guid, 'tracingStart', $params);
        $this->processVoidResponse($response);
        $this->isTracing = true;
    }

    /**
     * Start chunk
     *
     * @param  array<string, mixed>  $params
     */
    public function startChunk(array $params = []): void
    {
        if ($this->isTracingChunk) {
            return;
        }
        $response = Client::instance()->execute($this->guid, 'tracingStartChunk', $params);
        $this->processVoidResponse($response);
        $this->isTracingChunk = true;
    }

    /**
     * Stop chunk
     *
     * @param  array<string, mixed>  $params
     */
    public function stopChunk(array $params = []): Artifact
    {
        $response = Client::instance()->execute($this->guid, 'tracingStopChunk', $params);

        /** @var array{result: array{artifact: array{guid: string|null}}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['artifact']['guid'])) {
                $artifact = new Artifact($message['result']['artifact']['guid']);
            }
        }

        assert(isset($artifact), 'Tracing artifact was not created');

        $this->isTracingChunk = false;

        return $artifact;
    }

    /**
     * Stop tracing
     *
     * @param  array<string, mixed>  $params
     */
    public function stop(array $params = []): void
    {
        $response = Client::instance()->execute($this->guid, 'tracingStop', $params);
        $this->processVoidResponse($response);

        $this->isTracing = false;
    }

    public function close(): void
    {
        if ($this->isTracingChunk) {
            if (Playwright::tracingOption() === TracingOption::OFF) {
                $this->stopChunk([
                    'mode' => 'discard',
                ]);
            } else {
                $artifact = $this->stopChunk([
                    'mode' => 'archive',
                ]);

                if (is_dir(self::dir()) === false) {
                    @mkdir(self::dir(), 0755, true);
                }

                $artifact->saveAs(['path' => self::path()]);
            }
        }

        if ($this->isTracing) {
            $this->stop();
        }
    }
}
