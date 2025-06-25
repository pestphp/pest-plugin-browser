<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers\Laravel;

use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Pest\Browser\Contracts\HttpServer;
use Pest\Browser\Exceptions\ServerNotFoundException;
use Pest\Browser\Execution;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\HttpServer as ReactHttpServer;
use React\Http\Message\Response;
use React\Http\Message\Uri;
use React\Http\Middleware\LimitConcurrentRequestsMiddleware;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Middleware\RequestBodyParserMiddleware;
use React\Http\Middleware\StreamingRequestMiddleware;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use React\Stream\ReadableResourceStream;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\Mime\MimeTypes;
use Throwable;

/**
 * @codeCoverageIgnore
 */
final class LaravelHttpServer implements HttpServer
{
    /**
     * The underlying socket server instance, if any.
     */
    private ?SocketServer $socket = null;

    /**
     * The current connections to the server. Remember to flush
     * them when the server is being used between tests or sessions.
     *
     * @var array<int, ConnectionInterface>
     */
    private array $connections = [];

    /**
     * The original asset URL, if set.
     */
    private ?string $originalAssetUrl = null;

    /**
     * Creates a new laravel http server instance.
     */
    public function __construct(
        private readonly LoopInterface $loop,
        private readonly HttpFoundationFactory $factory,
        public readonly string $host,
        public readonly int $port,
    ) {
        //
    }

    /**
     * Destroy the server instance and stop listening for incoming connections.
     */
    public function __destruct()
    {
        $this->stop(); // @codeCoverageIgnore
    }

    /**
     * Rewrite the given URL to match the server's host and port.
     */
    public function rewrite(string $url): string
    {
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = mb_ltrim($url, '/');

            $url = '/'.$url;
        }

        $serverUri = new Uri($this->url());

        return (string) (new Uri($url))
            ->withScheme($serverUri->getScheme())
            ->withHost($serverUri->getHost())
            ->withPort($serverUri->getPort());
    }

    /**
     * Start the server and listen for incoming connections.
     */
    public function start(): void
    {
        if ($this->socket instanceof SocketServer) {
            return;
        }

        $this->socket = new SocketServer(
            "{$this->host}:{$this->port}",
            [],
            $this->loop,
        );

        $this->socket->on('connection', function (ConnectionInterface $connection): ConnectionInterface {
            $this->connections[] = $connection;

            $connection->on('close', function () use ($connection): void {
                $index = array_search($connection, $this->connections, true);

                if ($index !== false) {
                    unset($this->connections[$index]);
                }
            });

            return $connection;
        });

        $server = new ReactHttpServer(
            $this->loop,
            new StreamingRequestMiddleware(),
            new LimitConcurrentRequestsMiddleware(100),
            new RequestBodyBufferMiddleware(32 * 1024 * 1024),
            new RequestBodyParserMiddleware(32 * 1024 * 1024, 100),
            $this->handleRequest(...),
        );

        $server->listen($this->socket);
    }

    /**
     * Stop the server and close all connections.
     */
    public function stop(): void
    {
        // @codeCoverageIgnoreStart
        if ($this->socket instanceof SocketServer) {
            $this->flush();

            if ($this->socket instanceof SocketServer) {
                $this->socket->close();

                $this->socket = null;
            }
        }
    }

    /**
     * Flush pending requests and close all connections.
     */
    public function flush(): void
    {
        if (! $this->socket instanceof SocketServer) {
            return;
        }

        $this->loop->futureTick(fn () => $this->loop->stop());
        $this->loop->run();

        foreach ($this->connections as $connection) {
            $connection->close();
        }

        $this->connections = [];
    }

    /**
     * Get the public path for the given path.
     */
    public function url(): string
    {
        if (! $this->socket instanceof SocketServer) {
            throw new ServerNotFoundException('The HTTP server is not running.');
        }

        return sprintf('http://%s:%d', $this->host, $this->port);
    }

    /**
     * Sets the original asset URL.
     */
    public function setOriginalAssetUrl(string $url): void
    {
        $this->originalAssetUrl = mb_rtrim($url, '/');
    }

    /**
     * Handle the incoming request and return a response.
     *
     * @return PromiseInterface<Response>
     */
    private function handleRequest(ServerRequestInterface $request): PromiseInterface
    {
        $filepath = public_path($request->getUri()->getPath());

        if (file_exists($filepath) && ! is_dir($filepath)) {
            return $this->asset($filepath);
        }

        return $this->pipe(
            Request::createFromBase($this->factory->createRequest($request)),
        )->catch(static fn (Throwable $exception) => Response::plaintext(
            $exception->getMessage()."\n".$exception->getTraceAsString()
        )->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR));
    }

    /**
     * Pipe the request to the Laravel HTTP kernel and return the response.
     *
     * @return PromiseInterface<Response>
     */
    private function pipe(Request $request): PromiseInterface
    {
        // @phpstan-ignore-next-line
        return new Promise(function (callable $resolve) use ($request): void {
            if (class_exists(\Tighten\Ziggy\BladeRouteGenerator::class)) {
                \Tighten\Ziggy\BladeRouteGenerator::$generated = false;
            }

            if (app()->resolved(\Livewire\LivewireManager::class)) {
                $manager = app()->make(\Livewire\LivewireManager::class);

                // @phpstan-ignore-next-line
                if (method_exists($manager, 'flushState')) {
                    $manager->flushState();
                }
            }

            // @phpstan-ignore-next-line
            if (app()->resolved(\Inertia\ResponseFactory::class)) {
                // @phpstan-ignore-next-line
                $factory = app()->make(\Inertia\ResponseFactory::class);

                if (method_exists($factory, 'flushShared')) {
                    // @phpstan-ignore-next-line
                    $factory->flushShared();
                }
            }

            if (Execution::instance()->isPaused() === false) {
                $this->loop->futureTick(fn () => $this->loop->stop());
            }

            $kernel = app()->make(HttpKernel::class);

            $response = $kernel->handle($request);

            $content = $response->getContent();

            if ($content === false) {
                try {
                    ob_start();
                    $response->sendContent();
                } finally {
                    // @phpstan-ignore-next-line
                    $content = mb_trim(ob_get_clean());
                }
            }

            $resolve(new Response(
                $response->getStatusCode(),
                $response->headers->all(), // @phpstan-ignore-line
                $content,
                $response->getProtocolVersion(),
            ));

            $kernel->terminate($request, $response);
        });
    }

    /**
     * Return an asset response.
     *
     * @return PromiseInterface<Response>
     */
    private function asset(string $filepath): PromiseInterface
    {
        // @phpstan-ignore-next-line
        return new Promise(function (callable $resolve) use ($filepath): void {
            $file = fopen($filepath, 'r');

            if ($file === false) {
                $resolve(new Response(status: 404));

                return;
            }

            $mimeTypes = new MimeTypes();
            $contentType = $mimeTypes->getMimeTypes(pathinfo($filepath, PATHINFO_EXTENSION));

            $contentType = $contentType[0] ?? 'application/octet-stream';

            if (str_ends_with($filepath, '.js')) {
                $temporaryStream = fopen('php://temp', 'r+');
                assert($temporaryStream !== false, 'Failed to open temporary stream.');

                // @phpstan-ignore-next-line
                $temporaryContent = fread($file, (int) filesize($filepath));

                assert($temporaryContent !== false, 'Failed to open temporary stream.');

                $content = $this->rewriteAssetUrl($temporaryContent);

                fwrite($temporaryStream, $content);

                rewind($temporaryStream);

                $file = $temporaryStream;
            }

            $resolve(new Response(200, [
                'Content-Type' => $contentType,
            ], new ReadableResourceStream($file)));
        });
    }

    /**
     * Rewrite the asset URL in the given content.
     */
    private function rewriteAssetUrl(string $content): string
    {
        if ($this->originalAssetUrl === null) {
            return $content;
        }

        return str_replace($this->originalAssetUrl, $this->url(), $content);
    }
}
