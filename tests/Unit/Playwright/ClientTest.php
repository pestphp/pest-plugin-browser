<?php

declare(strict_types=1);

use Amp\ByteStream\ReadableStream;
use Amp\Cancellation;
use Amp\Http\Client\Response;
use Amp\Socket\SocketAddress;
use Amp\Socket\TlsInfo;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketCloseCode;
use Amp\Websocket\WebsocketCloseInfo;
use Amp\Websocket\WebsocketCount;
use Amp\Websocket\WebsocketMessage;
use Amp\Websocket\WebsocketTimestamp;
use Pest\Browser\Playwright\Client;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * A client whose connection answers the next request with the given messages, in order. The
 * placeholder "{id}" in a message stands for the id of that request.
 *
 * @param  array<int, array<string, mixed>>  $messages
 */
function clientAnsweringWith(array $messages): Client
{
    $connection = new class($messages) implements IteratorAggregate, WebsocketConnection
    {
        private string $requestId = '';

        /**
         * @param  array<int, array<string, mixed>>  $messages
         */
        public function __construct(private array $messages)
        {
            //
        }

        public function sendText(string $data): void
        {
            /** @var array{id: string} $request */
            $request = json_decode($data, true);

            $this->requestId = $request['id'];
        }

        public function receive(?Cancellation $cancellation = null): WebsocketMessage
        {
            $message = array_shift($this->messages);

            if ($message === null) {
                throw new LogicException('The request was answered, yet the client keeps reading.');
            }

            return WebsocketMessage::fromText(str_replace('{id}', $this->requestId, (string) json_encode($message)));
        }

        public function getIterator(): Iterator
        {
            yield from [];
        }

        public function getHandshakeResponse(): Response
        {
            throw new LogicException('Not part of the scripted exchange.');
        }

        public function getId(): int
        {
            return 0;
        }

        public function getLocalAddress(): SocketAddress
        {
            throw new LogicException('Not part of the scripted exchange.');
        }

        public function getRemoteAddress(): SocketAddress
        {
            throw new LogicException('Not part of the scripted exchange.');
        }

        public function getTlsInfo(): ?TlsInfo
        {
            return null;
        }

        public function getCloseInfo(): WebsocketCloseInfo
        {
            throw new LogicException('Not part of the scripted exchange.');
        }

        public function isCompressionEnabled(): bool
        {
            return false;
        }

        public function sendBinary(string $data): void
        {
            //
        }

        public function streamText(ReadableStream $stream): void
        {
            //
        }

        public function streamBinary(ReadableStream $stream): void
        {
            //
        }

        public function ping(): void
        {
            //
        }

        public function getCount(WebsocketCount $type): int
        {
            return 0;
        }

        public function getTimestamp(WebsocketTimestamp $type): float
        {
            return 0.0;
        }

        public function isClosed(): bool
        {
            return false;
        }

        public function close(int $code = WebsocketCloseCode::NORMAL_CLOSE, string $reason = ''): void
        {
            //
        }

        public function onClose(Closure $onClose): void
        {
            //
        }
    };

    $client = new Client;

    new ReflectionProperty(Client::class, 'websocketConnection')->setValue($client, $connection);

    return $client;
}

it('passes over the late error of an earlier request', function (): void {
    $client = clientAnsweringWith([
        ['id' => 'earlier', 'error' => ['error' => ['message' => 'Timeout 1000ms exceeded.']]],
        ['id' => '{id}', 'result' => ['value' => ['b' => true]]],
    ]);

    $messages = iterator_to_array($client->execute('frame@1', 'isVisible', ['selector' => '#x']), false);

    expect($messages)->toHaveCount(1)
        ->and($messages[0]['result']['value'])->toBe(['b' => true]);
});

it('passes over the late result of an earlier request', function (): void {
    $client = clientAnsweringWith([
        ['id' => 'earlier', 'result' => ['value' => ['b' => false]]],
        ['id' => '{id}', 'result' => ['value' => ['a' => []]]],
    ]);

    $messages = iterator_to_array($client->execute('frame@1', 'evaluateExpression', ['expression' => 'window.errors']), false);

    expect($messages)->toHaveCount(1)
        ->and($messages[0]['result']['value'])->toBe(['a' => []]);
});

it('throws the error of its own request', function (): void {
    $client = clientAnsweringWith([
        ['id' => '{id}', 'error' => ['error' => ['message' => 'Timeout 1000ms exceeded.']]],
    ]);

    expect(fn (): array => iterator_to_array($client->execute('frame@1', 'isVisible', ['selector' => '#x']), false))
        ->toThrow(ExpectationFailedException::class, 'Timeout 1000ms exceeded.');
});

it('hands on the events that arrive before its response', function (): void {
    $client = clientAnsweringWith([
        ['method' => '__create__', 'params' => ['type' => 'ElementHandle', 'guid' => 'handle@1']],
        ['id' => '{id}', 'result' => ['element' => ['guid' => 'handle@1']]],
    ]);

    $messages = iterator_to_array($client->execute('frame@1', 'querySelector', ['selector' => '#x']), false);

    expect($messages)->toHaveCount(2)
        ->and($messages[0]['method'])->toBe('__create__')
        ->and($messages[1]['result']['element']['guid'])->toBe('handle@1');
});
