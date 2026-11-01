<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Amp\ByteStream\ResourceStream;
use Amp\Http\Client\Response;
use Amp\Socket\Socket;
use Amp\Websocket\Client\Rfc6455ConnectionFactory;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketConnectionFactory;
use Amp\Websocket\Compression\WebsocketCompressionContext;
use Closure;

/**
 * Creates the WebSocket connection and hands its socket to the client.
 *
 * @internal
 */
final readonly class ConnectionFactory implements WebsocketConnectionFactory
{
    /**
     * Creates a new connection factory instance.
     *
     * @param  Closure(ResourceStream): void  $onSocket
     */
    public function __construct(
        private Closure $onSocket,
        private WebsocketConnectionFactory $factory = new Rfc6455ConnectionFactory(),
    ) {
        //
    }

    /**
     * Creates the connection, and reports its socket.
     */
    public function createConnection(
        Response $handshakeResponse,
        Socket $socket,
        ?WebsocketCompressionContext $compressionContext = null,
    ): WebsocketConnection {
        if ($socket instanceof ResourceStream) {
            ($this->onSocket)($socket);
        }

        return $this->factory->createConnection($handshakeResponse, $socket, $compressionContext);
    }
}
