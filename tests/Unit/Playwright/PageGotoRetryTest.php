<?php

declare(strict_types=1);

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Context;
use Pest\Browser\Playwright\Page;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Builds a page whose websocket answers each `goto` with the next message of the list:
 * a string is an error response, null a success.
 *
 * @param  array<int, string|null>  $answers
 * @param  array<int, string>  $sent  Receives the method of every request sent.
 */
function pageAnsweringGoto(array $answers, array &$sent): Page
{
    $connection = test()->createStub(WebsocketConnection::class);
    $requestId = null;

    $connection->method('sendText')->willReturnCallback(function (string $request) use (&$requestId, &$sent): void {
        $decoded = json_decode($request, true, flags: JSON_THROW_ON_ERROR);
        $requestId = $decoded['id'];
        $sent[] = $decoded['method'];
    });
    $connection->method('receive')->willReturnCallback(function () use (&$answers, &$requestId): WebsocketMessage {
        $answer = array_shift($answers);

        return WebsocketMessage::fromText(json_encode(is_string($answer)
            ? ['id' => $requestId, 'error' => ['error' => ['message' => $answer]]]
            : ['id' => $requestId, 'result' => []], JSON_THROW_ON_ERROR));
    });

    $client = new Client;
    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);
    new ReflectionProperty(Client::class, 'instance')->setValue(null, $client);

    return new Page(
        new ReflectionClass(Context::class)->newInstanceWithoutConstructor(),
        'page-guid',
        'frame-guid',
    );
}

beforeEach(function (): void {
    $this->previousClient = new ReflectionProperty(Client::class, 'instance')->getValue();
});

afterEach(function (): void {
    new ReflectionProperty(Client::class, 'instance')->setValue(null, $this->previousClient);
});

it('sends a navigation again after net::ERR_NO_BUFFER_SPACE', function (): void {
    $sent = [];
    $page = pageAnsweringGoto([
        'net::ERR_NO_BUFFER_SPACE at http://127.0.0.1:8000/',
        'net::ERR_NO_BUFFER_SPACE at http://127.0.0.1:8000/',
        null,
    ], $sent);

    expect($page->goto('http://127.0.0.1:8000/'))->toBe($page)
        ->and($sent)->toBe(['goto', 'goto', 'goto']);
});

it('throws net::ERR_NO_BUFFER_SPACE once the retries are used', function (): void {
    $sent = [];
    $page = pageAnsweringGoto(array_fill(0, 4, 'net::ERR_NO_BUFFER_SPACE at http://127.0.0.1:8000/'), $sent);

    expect(fn (): Page => $page->goto('http://127.0.0.1:8000/'))
        ->toThrow(ExpectationFailedException::class, 'net::ERR_NO_BUFFER_SPACE')
        ->and($sent)->toBe(['goto', 'goto', 'goto']);
});

it('throws any other navigation error on its first occurrence', function (): void {
    $sent = [];
    $url = 'http://127.0.0.1:8000/net::ERR_NO_BUFFER_SPACE';
    $page = pageAnsweringGoto(['net::ERR_CONNECTION_REFUSED at '.$url, null], $sent);

    expect(fn (): Page => $page->goto($url))
        ->toThrow(ExpectationFailedException::class, 'net::ERR_CONNECTION_REFUSED')
        ->and($sent)->toBe(['goto']);
});
