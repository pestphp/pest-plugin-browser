<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use DateTimeInterface;
use Generator;
use Pest\Browser\Playwright\Concerns\InteractsWithPlaywright;

/**
 * @internal
 */
final readonly class Clock
{
    use InteractsWithPlaywright;

    /**
     * Creates a new clock instance.
     */
    public function __construct(
        private string $targetGuid,
    ) {
        //
    }

    /**
     * Install fake implementations for time-related functions.
     *
     * @param  array<string, mixed>|null  $options  Options including time to initialize with
     */
    public function install(?array $options = null): void
    {
        $params = [];

        if (isset($options['time'])) {
            $params['time'] = $this->normalizeTime($options['time']);
        }

        $response = $this->sendMessage('clockInstall', $params);

        $this->processVoidResponse($response);
    }

    /**
     * Advance the clock, firing all the time-related callbacks.
     *
     * @param  int|string  $ticks  Time in milliseconds or human-readable string like "30:00"
     */
    public function runFor(int|string $ticks): void
    {
        $response = $this->sendMessage('clockRunFor', ['ticks' => $ticks]);

        $this->processVoidResponse($response);
    }

    /**
     * Advance the clock by jumping forward in time.
     * Only fires due timers at most once.
     *
     * @param  int|string  $ticks  Time in milliseconds or human-readable string like "30:00"
     */
    public function fastForward(int|string $ticks): void
    {
        $response = $this->sendMessage('clockFastForward', ['ticks' => $ticks]);

        $this->processVoidResponse($response);
    }

    /**
     * Advance the clock by jumping forward in time and pause the time.
     * Once called, no timers are fired unless other clock methods are called.
     *
     * @param  int|string|DateTimeInterface  $time  Time to pause at
     */
    public function pauseAt(int|string|DateTimeInterface $time): void
    {
        $response = $this->sendMessage('clockPauseAt', ['time' => $this->normalizeTime($time)]);

        $this->processVoidResponse($response);
    }

    /**
     * Resumes timers. Once called, time resumes flowing and timers fire as usual.
     */
    public function resume(): void
    {
        $response = $this->sendMessage('clockResume');

        $this->processVoidResponse($response);
    }

    /**
     * Makes Date.now and new Date() return fixed fake time at all times.
     * Keeps all the timers running.
     *
     * @param  int|string|DateTimeInterface  $time  Time to be set
     */
    public function setFixedTime(int|string|DateTimeInterface $time): void
    {
        $response = $this->sendMessage('clockSetFixedTime', ['time' => $this->normalizeTime($time)]);

        $this->processVoidResponse($response);
    }

    /**
     * Sets system time but does not trigger any timers.
     * Use this to test how the web page reacts to a time shift.
     *
     * @param  int|string|DateTimeInterface  $time  Time to be set
     */
    public function setSystemTime(int|string|DateTimeInterface $time): void
    {
        $response = $this->sendMessage('clockSetSystemTime', ['time' => $this->normalizeTime($time)]);

        $this->processVoidResponse($response);
    }

    /**
     * Normalize time parameter to the format expected by Playwright.
     */
    private function normalizeTime(int|string|DateTimeInterface $time): int|string
    {
        if ($time instanceof DateTimeInterface) {
            return $time->format('c'); // ISO 8601 format
        }

        return $time;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function sendMessage(string $method, array $params = []): Generator
    {
        return Client::instance()->execute($this->targetGuid, $method, $params);
    }
}
