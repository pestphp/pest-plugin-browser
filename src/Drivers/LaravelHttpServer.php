<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers;

use Amp\ByteStream\ReadableResourceStream;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\HttpServer as AmpHttpServer;
use Amp\Http\Server\HttpServerStatus;
use Amp\Http\Server\Request as AmpRequest;
use Amp\Http\Server\RequestHandler\ClosureRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\SocketHttpServer;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Testing\Concerns\WithoutExceptionHandlingHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Uri;
use Pest\Browser\Configuration;
use Pest\Browser\Contracts\HttpServer;
use Pest\Browser\Exceptions\ServerNotFoundException;
use Pest\Browser\Execution;
use Pest\Browser\GlobalState;
use Pest\Browser\Support\PersistHttpServer;
use Psr\Log\NullLogger;
use Symfony\Component\Mime\MimeTypes;
use Throwable;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class LaravelHttpServer implements HttpServer
{
    /**
     * The underlying socket server instance, if any.
     */
    private ?AmpHttpServer $socket = null;

    /**
     * The original asset URL, if set.
     */
    private ?string $originalAssetUrl = null;

    /**
     * The last throwable that occurred during the server's execution.
     */
    private ?Throwable $lastThrowable = null;

    /**
     * Creates a new laravel http server instance.
     */
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly ?string $bindAddress = null,
    ) {
        //
    }

    /**
     * Destroy the server instance and stop listening for incoming connections.
     */
    public function __destruct()
    {
        // @codeCoverageIgnoreStart
        // $this->stop();
    }

    /**
     * Create new instance from persisted data.
     */
    public static function fromPersisted(int $port): self
    {
        $persisted = PersistHttpServer::persisted();

        return new self($persisted['host'] ?? '127.0.0.1', $port, $persisted['bindAddress'] ?? null);
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

        $parts = parse_url($url);
        $queryParameters = [];
        $path = $parts['path'] ?? '/';
        parse_str($parts['query'] ?? '', $queryParameters);

        return (string) Uri::of($this->url())
            ->withPath($path)
            ->withQuery($queryParameters);
    }

    /**
     * Start the server and listen for incoming connections.
     */
    public function start(): void
    {
        if ($this->socket instanceof AmpHttpServer) {
            return;
        }

        $this->socket = $server = SocketHttpServer::createForDirectAccess(new NullLogger());

        $address = $this->bindAddress ?? $this->host;
        $server->expose("{$address}:{$this->port}");
        $server->start(
            new ClosureRequestHandler($this->handleRequest(...)),
            new DefaultErrorHandler(),
        );
    }

    /**
     * Stop the server and close all connections.
     */
    public function stop(): void
    {
        // @codeCoverageIgnoreStart
        if ($this->socket instanceof AmpHttpServer) {
            $this->flush();

            if ($this->socket instanceof AmpHttpServer) {
                if (in_array($this->socket->getStatus(), [HttpServerStatus::Starting, HttpServerStatus::Started], true)) {
                    $this->socket->stop();
                }

                $this->socket = null;
            }
        }
    }

    /**
     * Flush pending requests and close all connections.
     */
    public function flush(): void
    {
        if (! $this->socket instanceof AmpHttpServer) {
            return;
        }

        Execution::instance()->tick();

        $this->lastThrowable = null;
    }

    /**
     * Bootstrap the server and set the application URL.
     */
    public function bootstrap(): void
    {
        $this->start();

        $url = $this->url();

        config(['app.url' => $url]);

        config(['cors.paths' => ['*']]);

        if (app()->bound('url')) {
            $urlGenerator = app('url');

            assert($urlGenerator instanceof UrlGenerator);

            $this->setOriginalAssetUrl($urlGenerator->asset(''));

            $urlGenerator->useOrigin($url);
            $urlGenerator->useAssetOrigin($url);
            $urlGenerator->forceScheme('http');
        }
    }

    /**
     * Get the last throwable that occurred during the server's execution.
     */
    public function lastThrowable(): ?Throwable
    {
        return $this->lastThrowable;
    }

    /**
     * Throws the last throwable if it should be thrown.
     *
     * @throws Throwable
     */
    public function throwLastThrowableIfNeeded(): void
    {
        if (! $this->lastThrowable instanceof Throwable) {
            return;
        }

        $exceptionHandler = app(ExceptionHandler::class);

        if ($exceptionHandler instanceof WithoutExceptionHandlingHandler) {
            throw $this->lastThrowable;
        }
    }

    /**
     * Get the public path for the given path.
     */
    private function url(): string
    {
        if (! $this->socket instanceof AmpHttpServer) {
            $bindAddress = $this->bindAddress ?? $this->host.':'.$this->port;
            throw new ServerNotFoundException("The HTTP server on {$bindAddress} is not running.");
        }

        return sprintf('http://%s:%d', $this->host, $this->port);
    }

    /**
     * Sets the original asset URL.
     */
    private function setOriginalAssetUrl(string $url): void
    {
        $this->originalAssetUrl = mb_rtrim($url, '/');
    }

    /**
     * Handle the incoming request and return a response.
     */
    private function handleRequest(AmpRequest $request): Response
    {
        GlobalState::flush();

        if (Execution::instance()->isWaiting() === false) {
            Execution::instance()->tick();
        }

        $uri = $request->getUri();
        $path = in_array($uri->getPath(), ['', '0'], true) ? '/' : $uri->getPath();
        $query = $uri->getQuery() ?? ''; // @phpstan-ignore-line
        $fullPath = $path.($query !== '' ? '?'.$query : '');
        $absoluteUrl = mb_rtrim($this->url(), '/').$fullPath;

        $filepath = public_path($path);
        if (file_exists($filepath) && ! is_dir($filepath)) {
            return $this->asset($filepath);
        }

        $kernel = app()->make(HttpKernel::class);

        $contentType = $request->getHeader('content-type') ?? '';
        $method = mb_strtoupper($request->getMethod());
        $rawBody = (string) $request->getBody();
        $parameters = [];
        if ($method !== 'GET' && str_starts_with(mb_strtolower($contentType), 'application/x-www-form-urlencoded')) {
            parse_str($rawBody, $parameters);
        }

        $symfonyRequest = Request::create(
            $absoluteUrl,
            $method,
            $parameters,
            $request->getCookies(),
            [], // @TODO files...
            [], // @TODO server variables...
            $rawBody
        );

        $symfonyRequest->headers->add($request->getHeaders());

        if (isset(Configuration::$hostname)) {
            $symfonyRequest->headers->set('Host', sprintf('%s:%d', Configuration::$hostname, $this->port));
        }

        $debug = config('app.debug');

        try {
            config(['app.debug' => false]);

            $response = $kernel->handle($laravelRequest = Request::createFromBase($symfonyRequest));
        } catch (Throwable $e) {
            $this->lastThrowable = $e;

            throw $e;
        } finally {
            config(['app.debug' => $debug]);
        }

        $kernel->terminate($laravelRequest, $response);

        if (property_exists($response, 'exception') && $response->exception !== null) {
            assert($response->exception instanceof Throwable);

            $this->lastThrowable = $response->exception;
        }

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

        return new Response(
            $response->getStatusCode(),
            $response->headers->all(), // @phpstan-ignore-line
            $content,
        );
    }

    /**
     * Return an asset response.
     */
    private function asset(string $filepath): Response
    {
        $file = fopen($filepath, 'r');

        if ($file === false) {
            return new Response(404);
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

        return new Response(200, [
            'Content-Type' => $contentType,
        ], new ReadableResourceStream($file));
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
