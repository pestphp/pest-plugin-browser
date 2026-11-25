<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers;

use Amp\ByteStream\ReadableResourceStream;
use Amp\Http\Cookie\RequestCookie;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Driver\DefaultHttpDriverFactory;
use Amp\Http\Server\HttpServer as AmpHttpServer;
use Amp\Http\Server\HttpServerStatus;
use Amp\Http\Server\Request as AmpRequest;
use Amp\Http\Server\RequestHandler\ClosureRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\SocketHttpServer;
use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Session\Session;
use Illuminate\Cookie\CookieJar;
use Illuminate\Foundation\Testing\Concerns\WithoutExceptionHandlingHandler;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\CompiledRouteCollection;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Uri;
use Pest\Browser\Contracts\HttpServer;
use Pest\Browser\Exceptions\ServerNotFoundException;
use Pest\Browser\Execution;
use Pest\Browser\GlobalState;
use Pest\Browser\Playwright\Playwright;
use Psr\Http\Message\UriInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Mime\MimeTypes;
use Throwable;
use WeakMap;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class LaravelHttpServer implements HttpServer
{
    /**
     * The header the frontend bridge adds to the requests it forwards.
     */
    public const string BRIDGE_HEADER = 'x-pest-bridge';

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
     * Whether the readiness probe of the frontend server runs, so that the throwables of its requests are not recorded.
     */
    private ?string $probing = null;

    /**
     * The session stores each request of the frontend server created, per session manager, so that the frontend
     * server keeps its own session across its requests without the test seeing it.
     *
     * @var WeakMap<SessionManager, array{test: array<string, mixed>, stores: array<string, mixed>}>|null
     */
    private ?WeakMap $frontendSessionDrivers = null;

    /**
     * The scoped abstracts a request of the frontend server made the container notify, so that their rebound
     * callbacks learn the value of the test again once the whole state of the test is back.
     *
     * @var array<array-key, list<Closure>>
     */
    private array $reboundAfterRequest = [];

    /**
     * Creates a new laravel http server instance.
     */
    public function __construct(
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
        // @codeCoverageIgnoreStart
        // $this->stop();
    }

    /**
     * Rewrite the given URL to match the server's canonical host and port.
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

        // Increase the body size limit beyond Amp's 128-KiB
        // default to prevent deadlocks when reading large
        // JSON payloads in POST, PUT, and PATCH requests.
        $this->socket = $server = SocketHttpServer::createForDirectAccess(
            logger: $logger = new NullLogger(),
            httpDriverFactory: new DefaultHttpDriverFactory(
                logger: $logger,
                bodySizeLimit: 64 * 1024 * 1024,
            ),
        );

        $server->expose("{$this->host}:{$this->port}");
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
     * Starts the server and applies the configuration it needs to answer requests, without touching the URLs of the test.
     */
    public function serve(): void
    {
        $this->start();

        if (app()->bound('config')) {
            config(['cors.paths' => ['*']]);
        }
    }

    /**
     * Bootstrap the server and set the application URL.
     */
    public function bootstrap(): void
    {
        $this->serve();

        if (app()->bound('url')) {
            $urlGenerator = app('url');

            assert($urlGenerator instanceof UrlGenerator);

            $this->setOriginalAssetUrl($urlGenerator->asset(''));
            $urlGenerator->forceScheme('http');
        }

        $this->syncGeneratedUrls();
    }

    /**
     * Get the last throwable that occurred during the server's execution.
     */
    public function lastThrowable(): ?Throwable
    {
        return $this->lastThrowable;
    }

    /**
     * Starts ignoring the throwables of the requests of the frontend server on the given lane host while its readiness
     * probe runs, or stops with null: they belong to the start of the frontend server, not to an operation of the test.
     */
    public function probing(?string $probing): void
    {
        $this->probing = $probing;
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
     * Point Laravel's `app.url` and the URL generator's origin at the given URL, so that `route()`,
     * `asset()` and `config('app.url')` stay consistent with the host the browser is navigating to.
     */
    public function syncGeneratedUrls(?string $url = null): void
    {
        if (! $this->socket instanceof AmpHttpServer) {
            return;
        }

        // Guard against being called when the Laravel container is not (yet) bootstrapped,
        // e.g. from a Pest `beforeAll` hook, or between test files in a parallel worker
        // after the previous test's app has been torn down.
        if (! app()->bound('config')) {
            return;
        }

        $url ??= $this->url();

        config(['app.url' => $url]);

        if (app()->bound('url')) {
            $urlGenerator = app('url');

            assert($urlGenerator instanceof UrlGenerator);

            $urlGenerator->setRequest(Request::create($url));
            $urlGenerator->useOrigin($url);
            $urlGenerator->useAssetOrigin($url);
        }
    }

    /**
     * Whether a bridged request routed by the given lane host (empty when the bridge routed by none) may be a request
     * of the readiness probe of the frontend on the probed lane host: the same host name, in the ASCII form the bridge
     * gives it, and the same port when the lane host keeps one (the bridge keeps the port a header names).
     */
    private function mayBeProbe(string $laneHost, string $probing): bool
    {
        if ($laneHost === '') {
            return true;
        }

        [$name, $port] = $this->splitHost($laneHost);
        [$probedName, $probedPort] = $this->splitHost($probing);

        return $this->asciiHostName($name) === $this->asciiHostName($probedName)
            && ($port === null || $port === $probedPort);
    }

    /**
     * Splits a host into its name and its port, if any.
     *
     * @return array{string, string|null}
     */
    private function splitHost(string $host): array
    {
        return preg_match('/^(.*):(\d+)$/', $host, $parts) === 1 ? [$parts[1], $parts[2]] : [$host, null];
    }

    /**
     * Returns the host name percent-decoded and, when the intl functions are available, in the ASCII form of an
     * internationalized name, as the URL parser of the bridge writes it (UTS #46, nontransitional), in lower case.
     * The name goes to the conversion before any case change, which would turn a capital sigma into a final one. The
     * URL parser runs UTS #46 with CheckHyphens and VerifyDnsLength off, so the errors of those checks (hyphens at
     * the start, the end or the third and fourth places; empty labels; labels or names too long) are ignored. Each
     * label is converted on its own, split at the dots UTS #46 separates labels with, so that a long name stays
     * within the buffer of the conversion; a single label of some 250 characters or more is still past it.
     */
    private function asciiHostName(string $host): string
    {
        $host = rawurldecode($host);

        if (! function_exists('idn_to_ascii')) {
            return mb_strtolower($host);
        }

        $labels = preg_split('/[.\x{3002}\x{FF0E}\x{FF61}]/u', $host);

        return implode('.', array_map($this->asciiLabel(...), $labels === false ? [$host] : $labels));
    }

    /**
     * Returns one label of a host name in its ASCII form, in lower case.
     */
    private function asciiLabel(string $label): string
    {
        if ($label === '') {
            return '';
        }

        $ascii = idn_to_ascii($label, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46, $info);
        $skippedChecks = IDNA_ERROR_LEADING_HYPHEN | IDNA_ERROR_TRAILING_HYPHEN | IDNA_ERROR_HYPHEN_3_4
            | IDNA_ERROR_EMPTY_LABEL | IDNA_ERROR_LABEL_TOO_LONG | IDNA_ERROR_DOMAIN_NAME_TOO_LONG;

        $result = is_array($info) ? ($info['result'] ?? null) : null;
        $errors = is_array($info) ? ($info['errors'] ?? null) : null;

        // A label UTS #46 maps to nothing, such as a soft hyphen, comes back empty with an empty-label error.
        if ($ascii === false && is_string($result) && is_int($errors) && ($errors & ~$skippedChecks) === 0) {
            $ascii = $result;
        }

        return mb_strtolower(is_string($ascii) ? $ascii : $label);
    }

    /**
     * Get the base URL of the server.
     *
     * Uses the host configured with `withHost(...)` when set, so generated links
     * match the host the browser navigates to, and falls back to the bound IP.
     */
    private function url(): string
    {
        if (! $this->socket instanceof AmpHttpServer) {
            throw new ServerNotFoundException('The HTTP server is not running.');
        }

        return sprintf('http://%s:%d', Playwright::host() ?? $this->host, $this->port);
    }

    /**
     * Get the origin (scheme, host and port) the given request URI arrived on.
     */
    private function originOf(UriInterface $uri, ?string $bridgedHost = null): string
    {
        if ($uri->getHost() === '') {
            return mb_rtrim($this->url(), '/');
        }

        // A request of the frontend server carries the host of the frontend, whose port is the one its Host header
        // names, the default one when it names none (a browser never writes ":80"), never the port of the connection
        // to this server that Amp fills in for a Host without a port, or for ":80", which the URI drops.
        if ($bridgedHost !== null && $bridgedHost !== '') {
            $port = preg_match('/:(\d+)$/', $bridgedHost, $matches) === 1 ? (int) $matches[1] : 80;

            return $port === 80 ? sprintf('http://%s', $uri->getHost()) : sprintf('http://%s:%d', $uri->getHost(), $port);
        }

        return sprintf('http://%s:%d', $uri->getHost(), $uri->getPort() ?? $this->port);
    }

    /**
     * Sets the original asset URL.
     */
    private function setOriginalAssetUrl(string $url): void
    {
        // Captured once, before any sync re-points the generator's asset origin at this
        // server. Re-capturing later would record our own origin as the "original" and
        // stop asset rewriting from matching anything.
        $this->originalAssetUrl ??= mb_rtrim($url, '/');
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

        $rawBody = (string) $request->getBody();

        if (! $request->hasHeader(self::BRIDGE_HEADER)) {
            return $this->respond($request, $rawBody, fromFrontend: false);
        }

        $testUrl = app()->bound('config') ? config('app.url') : null;
        $urlGenerator = app()->bound('url') ? app('url') : null;
        $testUrlRequest = $urlGenerator instanceof UrlGenerator ? $urlGenerator->getRequest() : null;
        $testOrigins = $urlGenerator instanceof UrlGenerator ? $this->originsOf($urlGenerator) : null;
        $router = app()->bound('router') ? app('router') : null;
        $testRoute = $router instanceof Router ? $this->routeStateOf($router) : null;
        $testRouteInstance = $this->containerInstance(Route::class);

        try {
            return $this->respond($request, $rawBody, fromFrontend: true);
        } finally {
            if (is_string($testUrl)) {
                $this->syncGeneratedUrls($testUrl);
            }

            if ($urlGenerator instanceof UrlGenerator && $testUrlRequest instanceof Request) {
                $urlGenerator->setRequest($testUrlRequest);
            }

            if ($urlGenerator instanceof UrlGenerator && $testOrigins !== null) {
                $this->restoreOrigins($urlGenerator, $testOrigins);
            }

            if ($router instanceof Router && $testRoute !== null) {
                $this->restoreRouteState($router, $testRoute);
            }

            if ($testRouteInstance instanceof Route) {
                app()->instance(Route::class, $testRouteInstance);
            } else {
                app()->forgetInstance(Route::class);
            }

            $rebound = $this->reboundAfterRequest;
            $this->reboundAfterRequest = [];

            ContainerScopedInstances::rebound(app(), $rebound);
        }
    }

    /**
     * Returns the state of the URL generator a request of the frontend server may change, in the server or in its
     * middleware: every property but the request and the route URL generator (the request is put back
     * on its own), and the default route parameters of the route URL generator.
     *
     * @return array{properties: array<string, mixed>, defaults: array<array-key, mixed>}
     */
    private function originsOf(UrlGenerator $urlGenerator): array
    {
        $properties = [];

        foreach (new ReflectionClass($urlGenerator)->getProperties() as $property) {
            if (! $property->isStatic() && ! $property->isReadOnly() && $property->isInitialized($urlGenerator)
                && ! in_array($property->getName(), ['request', 'routeGenerator', 'cachedRoot', 'cachedScheme'], true)) {
                $properties[$property->getName()] = $property->getValue($urlGenerator);
            }
        }

        return ['properties' => $properties, 'defaults' => $urlGenerator->getDefaultParameters()];
    }

    /**
     * Puts back the state of the URL generator the test had, after its request was put back.
     *
     * @param  array{properties: array<string, mixed>, defaults: array<array-key, mixed>}  $origins
     */
    private function restoreOrigins(UrlGenerator $urlGenerator, array $origins): void
    {
        foreach ($origins['properties'] as $property => $value) {
            new ReflectionProperty($urlGenerator, $property)->setValue($urlGenerator, $value);
        }

        (function () use ($origins): void {
            $this->cachedRoot = null;
            $this->cachedScheme = null;
            $this->routeUrl()->defaultParameters = $origins['defaults'];
        })->call($urlGenerator);
    }

    /**
     * Returns the route, the request the router dispatched last and the route collection of the test, which a request
     * of the frontend server replaces (its middleware may call `Route::setRoutes()`, which also rebinds `routes`).
     *
     * @return array{route: Route|null, request: Request, routes: RouteCollectionInterface}
     */
    private function routeStateOf(Router $router): array
    {
        return (fn (): array => ['route' => $this->current, 'request' => $this->currentRequest, 'routes' => $this->routes])->call($router);
    }

    /**
     * Puts back the route, the request the router dispatched last and the route collection of the test, on the router
     * and on the container, whose `routes` rebinding hands the collection to the URL generator too.
     *
     * @param  array{route: Route|null, request: Request, routes: RouteCollectionInterface}  $state
     */
    private function restoreRouteState(Router $router, array $state): void
    {
        $replaced = $router->getRoutes() !== $state['routes'];

        (function () use ($state): void {
            $this->current = $state['route'];
            $this->currentRequest = $state['request'];
            $this->routes = $state['routes'];
        })->call($router);

        if ($replaced || $this->containerInstance('routes') !== $state['routes']) {
            app()->instance('routes', $state['routes']);
        }
    }

    /**
     * Whether the error of a request belongs to the test. While `Frontend::prepare()` waits for a dev server, the
     * requests of its readiness probe reach the backend through the bridge too; the bridge passes the lane host it
     * routed by, so that an error of a page of another frontend the test keeps open, on another host or port, is still
     * recorded. A request without a lane host (the frontend forwards none), with the probed host name and no port, or
     * of a page of an earlier frontend on the same host and port, cannot be told apart from the probe.
     */
    private function recordsThrowableOf(AmpRequest $request, bool $fromFrontend): bool
    {
        if (! $fromFrontend || $this->probing === null) {
            return true;
        }

        return ! $this->mayBeProbe($request->getHeader('x-pest-lane-host') ?? '', $this->probing);
    }

    /**
     * Answers the request, as a request of the frontend server or as a request of the test, without suspending
     * the fiber, so that a concurrent request cannot observe the state this request changes.
     */
    private function respond(AmpRequest $request, string $rawBody, bool $fromFrontend): Response
    {
        $uri = $request->getUri();
        $path = in_array($uri->getPath(), ['', '0'], true) ? '/' : $uri->getPath();
        $query = $uri->getQuery() ?? ''; // @phpstan-ignore-line
        $fullPath = $path.($query !== '' ? '?'.$query : '');

        // The browser reaches us on the host the test asked for, so trust the
        // request's own origin rather than the globally configured one. This
        // keeps a per-visit `withHost(...)` intact for the page's later
        // fetch/XHR requests, after the global host has been restored.
        $origin = $this->originOf($uri, $fromFrontend ? ($request->getHeader('host') ?? '') : null);
        $absoluteUrl = $origin.$fullPath;

        // Keep `route()`, `asset()` and `config('app.url')` aligned with the
        // origin the app is answering on for this request.
        $this->syncGeneratedUrls($origin);

        $filepath = public_path($path);
        if (file_exists($filepath) && ! is_dir($filepath)) {
            return $this->asset($filepath);
        }

        $kernel = app()->make(HttpKernel::class);

        $contentType = $request->getHeader('content-type') ?? '';
        $method = mb_strtoupper($request->getMethod());
        $parameters = [];
        $files = [];
        if ($method !== 'GET' && str_starts_with(mb_strtolower($contentType), 'application/x-www-form-urlencoded')) {
            parse_str($rawBody, $parameters);
        }
        if ($method !== 'GET' && preg_match('/^multipart\/form-data;.*boundary="?([^";]+)"?/i', $contentType, $boundary) === 1) {
            [$parameters, $files] = $this->parseMultipartBody($rawBody, $boundary[1]);
        }
        $cookies = array_map(fn (RequestCookie $cookie): string => urldecode($cookie->getValue()), $request->getCookies());
        $serverVariables = [];

        if (! $fromFrontend) {
            $cookies = array_merge($cookies, test()->prepareCookiesForRequest()); // @phpstan-ignore-line

            /** @var array<string, string> $serverVariables */
            $serverVariables = test()->serverVariables(); // @phpstan-ignore-line
        }

        $symfonyRequest = Request::create(
            $absoluteUrl,
            $method,
            $parameters,
            $cookies,
            $files,
            $serverVariables,
            $rawBody
        );

        $symfonyRequest->headers->add($request->getHeaders());

        $debug = config('app.debug');

        // The container is reused, so flush stale queued cookies (FPM/Octane start fresh).
        app()->make(CookieJar::class)->flushQueuedCookies();

        $scopedVersions = ContainerScopedInstances::versions(app());

        $testRequest = $fromFrontend && app()->bound('request') ? app('request') : null;
        $testUserResolver = $testRequest instanceof Request ? $testRequest->getUserResolver() : null;

        $testAuthentication = $fromFrontend ? $this->isolateAuthentication() : null;

        $testRegisteredInstances = $fromFrontend ? ContainerScopedInstances::registered(app()) : [];

        $testScopedInstances = $fromFrontend ? ContainerScopedInstances::withdraw(app()) : [];

        if ($fromFrontend) {
            ContainerScopedInstances::watchRebound(app(), array_keys($testScopedInstances + $testRegisteredInstances));
        }

        // However the request ends, so an exception in terminate or in a streamed response cannot keep them.
        try {
            try {
                config(['app.debug' => false]);

                $laravelRequest = Request::createFromBase($symfonyRequest);

                if ($fromFrontend) {
                    $this->forgetCachedControllers();
                }

                $response = $kernel->handle($laravelRequest);
            } catch (Throwable $e) {
                if ($this->recordsThrowableOf($request, $fromFrontend)) {
                    $this->lastThrowable = $e;
                }

                $this->removeUploads($files);

                throw $e;
            } finally {
                config(['app.debug' => $debug]);
            }

            $kernel->terminate($laravelRequest, $response);

            // PHP removes the temporary files at shutdown, after terminate: a listener reading an upload there still finds it.
            $this->removeUploads($files);

            if (property_exists($response, 'exception') && $response->exception !== null && $this->recordsThrowableOf($request, $fromFrontend)) {
                assert($response->exception instanceof Throwable);

                $this->lastThrowable = $response->exception;
            }

            $content = $response->getContent();

            if ($content === false) {
                try {
                    ob_start();
                    $response->sendContent();
                } finally {
                    $buffer = ob_get_clean();
                }

                $content = $buffer === false ? '' : $buffer;
            }
        } finally {
            if ($fromFrontend) {
                $this->reboundAfterRequest = ContainerScopedInstances::takeRebound(app());
            }

            $this->forgetScopedInstancesSince($scopedVersions);

            if ($testScopedInstances !== [] || $testRegisteredInstances !== []) {
                ContainerScopedInstances::restore(app(), $testScopedInstances, $testRegisteredInstances);

                Facade::clearResolvedInstances();
            }

            if (isset($laravelRequest)) {
                $this->forgetControllerOfAnsweringRoute($laravelRequest);
            }

            if ($testAuthentication instanceof ClientAuthentication) {
                $this->restoreAuthentication($testAuthentication);
            }

            if ($testRequest !== null) {
                app()->instance('request', $testRequest);
            }

            if ($testRequest instanceof Request && $testUserResolver instanceof Closure) {
                $testRequest->setUserResolver($testUserResolver);
            }

        }

        return new Response(
            $response->getStatusCode(),
            $response->headers->all(), // @phpstan-ignore-line
            $content,
        );
    }

    /**
     * Gives a request of the frontend server its own session and authenticated user, as a separate
     * client has in production, and returns the ones of the test so that they can be restored.
     */
    private function isolateAuthentication(): ClientAuthentication
    {
        $sessionStore = $this->containerInstance('session.store');
        $auth = app()->bound('auth') ? app('auth') : null;
        $session = $this->containerInstance('session.store')
            ?? (app()->bound('session.store') && config('session.driver') !== null ? app('session.store') : null);
        $manager = $this->sessionManager();

        $authentication = new ClientAuthentication(
            $auth instanceof AuthManager ? $auth : null,
            $session instanceof Session ? $session : null,
            $this->containerInstance('auth.driver'),
            $manager,
            $sessionStore,
        );

        $authentication->isolate();

        if ($manager instanceof SessionManager) {
            $authentication->useFrontendSessionDrivers($this->reusableFrontendSessionDrivers($manager));
        }

        app()->forgetInstance('auth.driver');
        app()->forgetInstance('session.store');

        Facade::clearResolvedInstances();

        return $authentication;
    }

    /**
     * Returns the session stores of the frontend server, per session manager.
     *
     * @return WeakMap<SessionManager, array{test: array<string, mixed>, stores: array<string, mixed>}>
     */
    private function frontendSessionDrivers(): WeakMap
    {
        return $this->frontendSessionDrivers ??= new WeakMap;
    }

    /**
     * Returns the session stores of the frontend server for the given manager, as long as the test kept its own stores,
     * the way Laravel keeps a built store until `forgetDrivers()`; with no store of the test to watch, none.
     *
     * @return array<string, mixed>
     */
    private function reusableFrontendSessionDrivers(SessionManager $manager): array
    {
        $saved = $this->frontendSessionDrivers()[$manager] ?? null;

        if ($saved === null) {
            return [];
        }

        $current = $manager->getDrivers();

        $kept = $saved['test'] !== []
            && array_all($saved['test'], static fn (mixed $store, string $driver): bool => array_key_exists($driver, $current) && $current[$driver] === $store);

        if (! $kept) {
            unset($this->frontendSessionDrivers()[$manager]);

            return [];
        }

        return $saved['stores'];
    }

    /**
     * Returns the session manager, building it when the test did not, which only reads the configuration.
     */
    private function sessionManager(): ?SessionManager
    {
        $manager = app()->bound('session') ? app('session') : null;

        return $manager instanceof SessionManager ? $manager : null;
    }

    /**
     * Restores the session and authenticated user of the test after a request of the frontend server.
     */
    private function restoreAuthentication(ClientAuthentication $authentication): void
    {
        $manager = $this->sessionManager();

        if ($manager instanceof SessionManager) {
            $frontendSessionDrivers = $this->frontendSessionDrivers();
            $frontendSessionDrivers[$manager] = [
                'test' => $authentication->testSessionDrivers(),
                'stores' => array_replace($frontendSessionDrivers[$manager]['stores'] ?? [], $authentication->frontendSessionDrivers()),
            ];
        }

        $authentication->restore();

        app()->forgetInstance('session.store');

        if ($authentication->sessionStoreInstance !== null) {
            app()->instance('session.store', $authentication->sessionStoreInstance);
        }

        app()->forgetInstance('auth.driver');

        if ($authentication->authDriver !== null) {
            app()->instance('auth.driver', $authentication->authDriver);
        }

        Facade::clearResolvedInstances();
    }

    /**
     * Returns the object the container holds as a shared instance under the given name, if any.
     */
    private function containerInstance(string $name): ?object
    {
        $instance = (fn (): mixed => $this->instances[$name] ?? null)->call(app());

        return is_object($instance) ? $instance : null;
    }

    /**
     * Forgets the controller the answering route cached, so that no later request reuses the
     * dependencies it resolved, as each request builds its own controller under PHP-FPM.
     */
    private function forgetControllerOfAnsweringRoute(Request $request): void
    {
        $router = app('router');

        foreach ([$request->route(), $router instanceof Router ? $router->current() : null] as $route) {
            if ($route instanceof Route) {
                $route->flushController();
            }
        }
    }

    /**
     * Forgets the controller every route cached, before a request of the frontend server runs, so that it never
     * reuses a controller of the test, whatever method, URI or request object a middleware gives the router.
     */
    private function forgetCachedControllers(): void
    {
        $router = app('router');

        if (! $router instanceof Router) {
            return;
        }

        $routes = $router->getRoutes();

        foreach ($routes->getRoutes() as $route) {
            $route->flushController();

            $name = $route->getName();

            if ($routes instanceof CompiledRouteCollection && $name !== null) {
                $routes->getByName($name)?->flushController();
            }
        }
    }

    /**
     * Return an asset response.
     */
    private function asset(string $filepath): Response
    {
        $mimeTypes = new MimeTypes();
        $contentType = $mimeTypes->getMimeTypes(pathinfo($filepath, PATHINFO_EXTENSION));

        $contentType = $contentType[0] ?? 'application/octet-stream';

        if (str_ends_with($filepath, '.js')) {
            $content = file_get_contents($filepath);

            if ($content === false) {
                return new Response(404);
            }

            // The content is sent as a string instead of a stream because a
            // "php://temp" stream is set to non-blocking mode, which makes
            // the event loop deliver the first 8192 bytes chunk only.
            return new Response(200, [
                'Content-Type' => $contentType,
            ], $this->rewriteAssetUrl($content));
        }

        $file = fopen($filepath, 'r');

        if ($file === false) {
            return new Response(404);
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
        $current = $this->url();

        if ($this->originalAssetUrl === null && app()->bound('config')) {
            $fallback = config('app.url', 'http://localhost');
            $fallback = is_string($fallback) ? $fallback : 'http://localhost';
            $this->originalAssetUrl = mb_rtrim($fallback, '/');

            if (app()->bound('url')) {
                try {
                    $urlGenerator = app('url');
                    assert($urlGenerator instanceof UrlGenerator);
                    $generated = $urlGenerator->asset('');
                    if ($generated !== '') {
                        $this->originalAssetUrl = mb_rtrim($generated, '/');
                    }
                } catch (Throwable) {
                    // Keep fallback.
                }
            }
        }

        if ($this->originalAssetUrl !== null && $this->originalAssetUrl !== $current) {
            $content = str_replace($this->originalAssetUrl, $current, $content);
        }

        $content = (string) preg_replace(
            '#https?://localhost(?::\d+)?#',
            $current,
            $content,
        );

        return str_replace(
            ['https://my-app.test', 'http://my-app.test'],
            $current,
            $content,
        );
    }

    /**
     * Remove the temporary files of the uploads.
     *
     * @param  array<string, UploadedFile>  $files
     */
    private function removeUploads(array $files): void
    {
        foreach ($files as $file) {
            @unlink($file->getPathname());
        }
    }

    /**
     * Forget the scoped instances resolved while serving a request, as FPM and Octane
     * start each request without them. The ones the test bound before the request,
     * such as a mock, stay bound, like they do with `$this->get()`.
     *
     * @param  array<array-key, int>  $before
     */
    private function forgetScopedInstancesSince(array $before): void
    {
        $forgotten = false;

        foreach (ContainerScopedInstances::versions(app()) as $abstract => $version) {
            if (($before[$abstract] ?? null) !== $version) {
                app()->forgetInstance((string) $abstract);

                $forgotten = true;
            }
        }

        // Facades cache what they resolve, so drop those references as well (the queue worker does the same).
        if ($forgotten) {
            Facade::clearResolvedInstances();
        }
    }

    /**
     * Reads a multipart body the way PHP does for a real upload: the fields
     * into the parameters, each file into a temporary file wrapped in an
     * upload in test mode (there is no real upload for `is_uploaded_file()`
     * to recognise here). Two parts with the same name keep the last file,
     * and a nested name such as `files[avatar]` stays flat where PHP would
     * nest it.
     *
     * @return array{0: array<int|string, mixed>, 1: array<string, UploadedFile>}
     */
    private function parseMultipartBody(string $body, string $boundary): array
    {
        $fields = '';
        $files = [];

        foreach (explode("--{$boundary}", $body) as $part) {
            // Without '8bit', mbstring re-encodes the part and rewrites non-UTF-8 bytes as "?".
            $part = mb_ltrim($part, "\r\n", '8bit');
            if ($part === '' || str_starts_with($part, '--')) {
                continue;
            }

            [$rawHeaders, $content] = array_pad(explode("\r\n\r\n", $part, 2), 2, '');
            $content = preg_replace('/\r\n$/', '', $content) ?? $content;
            if (preg_match('/;\s*name="([^"]*)"/', $rawHeaders, $name) !== 1) {
                continue;
            }

            if (preg_match('/filename="([^"]*)"/', $rawHeaders, $fileName) === 1) {
                $path = (string) tempnam(sys_get_temp_dir(), 'pest-upload-');
                file_put_contents($path, $content);
                $mimeType = preg_match('/Content-Type:\s*([^\r\n]+)/i', $rawHeaders, $type) === 1 ? mb_trim($type[1]) : null;
                $files[$name[1]] = new UploadedFile($path, $fileName[1], $mimeType, null, true);

                continue;
            }

            // Encoded then parsed as a query string, so that `items[]` and `a[b]` nest as they would.
            $fields .= ($fields === '' ? '' : '&').rawurlencode($name[1]).'='.rawurlencode($content);
        }

        $parameters = [];
        parse_str($fields, $parameters);

        return [$parameters, $files];
    }
}
