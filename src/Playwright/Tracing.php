<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Support\Trace;

/**
 * @internal
 */
final class Tracing
{
    use Concerns\InteractsWithPlaywright;

    /**
     * The tracings that are currently recording.
     *
     * @var array<int, self>
     */
    private static array $recording = [];

    /**
     * The path the trace will be saved to, if the test fails.
     */
    private ?string $path = null;

    /**
     * Creates a new tracing instance.
     */
    public function __construct(
        private readonly string $guid,
    ) {
        //
    }

    /**
     * Whether any tracing is currently recording.
     */
    public static function isRecording(): bool
    {
        return self::$recording !== [];
    }

    /**
     * Stops every tracing that is recording, saving the traces when requested.
     *
     * @return array<int, string>
     */
    public static function stopAll(bool $save): array
    {
        $paths = [];

        foreach (self::$recording as $tracing) {
            $path = $tracing->stop($save);

            if ($path !== null) {
                $paths[] = $path;
            }
        }

        self::$recording = [];

        Client::instance()->flushStacks();

        return $paths;
    }

    /**
     * Starts recording a trace, with screenshots and DOM snapshots.
     */
    public function start(string $title): void
    {
        $this->processVoidResponse($this->sendMessage('tracingStart', [
            'screenshots' => true,
            'snapshots' => true,
        ]));

        $this->processVoidResponse($this->sendMessage('tracingStartChunk', [
            'title' => $title,
        ]));

        $this->path = Trace::path($title, count(self::$recording));

        self::$recording[] = $this;
    }

    /**
     * Gets the path the trace will be saved to, if it is recording.
     */
    public function path(): ?string
    {
        return $this->path;
    }

    /**
     * Groups the calls made by the given callback under the given name.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function group(string $name, ?string $file, ?int $line, callable $callback): mixed
    {
        if ($this->path === null) {
            return $callback();
        }

        $location = $file === null ? [] : ['location' => ['file' => $file, 'line' => $line ?? 0]];

        $this->processVoidResponse($this->sendMessage('tracingGroup', ['name' => $name, ...$location]));

        try {
            return $callback();
        } finally {
            $this->processVoidResponse($this->sendMessage('tracingGroupEnd'));
        }
    }

    /**
     * Stops recording the trace, and saves it when requested.
     */
    private function stop(bool $save): ?string
    {
        $path = $this->path;

        $this->path = null;

        if ($path === null) {
            return null;
        }

        $response = $this->sendMessage('tracingStopChunk', ['mode' => $save ? 'archive' : 'discard']);

        $artifactGuid = null;

        /** @var array{result?: array{artifact?: array{guid?: string}}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['artifact']['guid'])) {
                $artifactGuid = $message['result']['artifact']['guid'];
            }
        }

        $this->processVoidResponse($this->sendMessage('tracingStop'));

        if ($artifactGuid === null) {
            return null;
        }

        // The archive lives on the Playwright server, which may not share our filesystem, so it is streamed back.
        $archive = new Artifact($artifactGuid)->contents();

        Trace::save($path, $archive, Client::instance()->stacks());

        return $path;
    }
}
