<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

use Amp\ByteStream\StreamException;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\HttpException;
use Amp\Http\Client\Request;

use function Amp\delay;

/**
 * @internal
 */
final class Network
{
    /**
     * The client that sends the requests, without retries or redirects.
     */
    private static ?HttpClient $client = null;

    /**
     * Determines whether something accepts connections on the given port of one of the given hosts.
     *
     * @param  list<string>  $hosts
     */
    public static function isListening(int $port, float $timeout = 1.0, array $hosts = ['127.0.0.1']): bool
    {
        foreach ($hosts as $host) {
            $socket = @fsockopen(self::urlHost($host), $port, $errorCode, $errorMessage, $timeout);

            if ($socket !== false) {
                fclose($socket);

                return true;
            }
        }

        return false;
    }

    /**
     * Returns the host as it is written in a URL, with brackets around an IPv6 address.
     */
    public static function urlHost(string $host): string
    {
        return str_contains($host, ':') && ! str_starts_with($host, '[') ? "[{$host}]" : $host;
    }

    /**
     * Sends a GET request and returns its status, or null when nothing answers in time; the body is not read, so a
     * page of any size answers.
     *
     * The request runs on the event loop, so the HTTP server of this process keeps answering while it waits.
     *
     * @param  array<non-empty-string, string>  $headers
     */
    public static function status(string $url, float $timeout, array $headers = []): ?int
    {
        $request = self::request($url, $timeout, $headers);

        try {
            $response = self::client()->request($request);
            $response->getBody()->close();

            return $response->getStatus();
        } catch (HttpException|StreamException) {
            return null;
        }
    }

    /**
     * Sends a GET request and returns its status and body, or null when nothing answers in time.
     *
     * The request runs on the event loop, so the HTTP server of this process keeps answering while it waits.
     *
     * @param  array<non-empty-string, string>  $headers
     * @return array{status: int, body: string}|null
     */
    public static function get(string $url, float $timeout, array $headers = []): ?array
    {
        $request = self::request($url, $timeout, $headers);

        try {
            $response = self::client()->request($request);

            return ['status' => $response->getStatus(), 'body' => $response->getBody()->buffer()];
        } catch (HttpException|StreamException) {
            return null;
        }
    }

    /**
     * Waits until the condition holds or the deadline passes, giving the condition the seconds that remain.
     *
     * The wait runs the event loop, so the HTTP server of this process keeps answering while it waits.
     *
     * @param  callable(float): bool  $condition
     */
    public static function waitUntil(callable $condition, float $seconds): bool
    {
        $deadline = microtime(true) + $seconds;

        do {
            if ($condition(max(0.1, $deadline - microtime(true)))) {
                return true;
            }

            delay(0.2);
        } while (microtime(true) < $deadline);

        return false;
    }

    /**
     * Builds a GET request with the given timeout and headers.
     *
     * @param  array<non-empty-string, string>  $headers
     */
    private static function request(string $url, float $timeout, array $headers): Request
    {
        $request = new Request($url);
        $request->setTcpConnectTimeout($timeout);
        $request->setTransferTimeout($timeout);
        $request->setInactivityTimeout($timeout);

        foreach ($headers as $name => $value) {
            $request->setHeader($name, $value);
        }

        return $request;
    }

    /**
     * Returns the client that sends the requests.
     */
    private static function client(): HttpClient
    {
        return self::$client ??= (new HttpClientBuilder)->retry(0)->followRedirects(0)->build();
    }
}
