<?php

declare(strict_types=1);

use Amp\CancelledException;
use Amp\TimeoutCancellation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Drivers\LaravelHttpServer;
use Pest\Browser\ServerManager;

use function Amp\Socket\connect;

/**
 * Everything the server writes for one request, read raw because a browser assertion cannot see it:
 * Chromium discards a stray chunk terminator that has already arrived, and fails the next request on
 * the socket when it has not, so the same bug passes or fails with timing.
 */
function bodilessResponseWireTo(string $path): string
{
    $http = ServerManager::instance()->http();
    assert($http instanceof LaravelHttpServer);

    $socket = connect("127.0.0.1:{$http->port}");
    $socket->write("GET {$path} HTTP/1.1\r\nHost: 127.0.0.1\r\nConnection: keep-alive\r\n\r\n");

    $raw = '';

    try {
        while (($chunk = $socket->read(new TimeoutCancellation(0.4))) !== null) {
            $raw .= $chunk;
        }
    } catch (CancelledException) {
        // Silence is the end of the response: the connection stays open on keep-alive.
    }

    $socket->close();

    return $raw;
}

it('writes nothing after the headers of a response that has no body', function (int $status): void {
    Route::get('/', fn (): string => 'home');
    Route::get('/bodiless', fn (): Response => new Response('', $status));

    // Starts the in-process server; the response under test is then read off the wire.
    visit('/')->assertSee('home');

    $raw = bodilessResponseWireTo('/bodiless');

    expect($raw)->toStartWith("HTTP/1.1 {$status} ")
        ->and($raw)->toEndWith("\r\n\r\n")
        // The bytes Chromium reads as the start of the next response on the socket.
        ->and($raw)->not->toContain("0\r\n\r\n")
        ->and(mb_strtolower($raw))->not->toContain('transfer-encoding: chunked');
})->with([204, 304]);

it('writes a response that has a body with its length', function (): void {
    Route::get('/', fn (): string => 'home');
    Route::get('/body', fn (): string => 'hello');

    visit('/')->assertSee('home');

    $raw = bodilessResponseWireTo('/body');

    expect($raw)->toStartWith('HTTP/1.1 200 ')
        ->and(mb_strtolower($raw))->toContain("content-length: 5\r\n")
        ->and($raw)->toEndWith("\r\n\r\nhello");
});
