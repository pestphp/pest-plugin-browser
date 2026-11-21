<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

/**
 * @internal
 */
final readonly class Network
{
    /**
     * Determines whether something accepts connections on the given port.
     */
    public static function isListening(int $port, float $timeout = 1.0): bool
    {
        $socket = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, $timeout);

        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }

    /**
     * Sends a GET request and returns its status and body, or null when nothing answers.
     *
     * @return array{status: int, body: string}|null
     */
    public static function get(string $url, float $timeout): ?array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'ignore_errors' => true,
                'follow_location' => 0,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);

        if ($body === false) {
            return null;
        }

        $headers = http_get_last_response_headers() ?? [];

        $status = preg_match('#^HTTP/\S+\s+(\d{3})#', $headers[0] ?? '', $matches) === 1
            ? (int) $matches[1]
            : 0;

        return ['status' => $status, 'body' => $body];
    }

    /**
     * Waits until the condition holds or the deadline passes.
     *
     * @param  callable(): bool  $condition
     */
    public static function waitUntil(callable $condition, float $seconds): bool
    {
        $deadline = microtime(true) + $seconds;

        do {
            if ($condition()) {
                return true;
            }

            usleep(200_000);
        } while (microtime(true) < $deadline);

        return false;
    }
}
