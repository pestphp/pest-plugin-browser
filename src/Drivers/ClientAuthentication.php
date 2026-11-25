<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Session\Session;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;

/**
 * @internal
 */
final readonly class ClientAuthentication
{
    /**
     * The key under which the container holds the default guard the application configured.
     */
    public const string CONFIGURED_GUARD = 'pest.browser.configured-guard';

    /**
     * The identifier of the session of the client.
     */
    private ?string $sessionId;

    /**
     * Whether the session of the client was started.
     */
    private bool $sessionStarted;

    /**
     * The attributes of the session of the client.
     *
     * @var array<array-key, mixed>
     */
    private array $sessionAttributes;

    /**
     * The guards the client resolved, with their authenticated users.
     *
     * @var array<array-key, mixed>
     */
    private array $guards;

    /**
     * The configured default guard of the client, as the configuration holds it, null included.
     */
    private mixed $defaultGuard;

    /**
     * The callback that resolves the authenticated user of the client.
     */
    private ?Closure $userResolver;

    /**
     * The session stores the session manager created, keyed by driver, as the manager holds them.
     *
     * @var array<string, mixed>
     */
    private array $sessionDrivers;

    /**
     * The snapshots of the session stores of the manager that are not the session of the client, one per store.
     *
     * @var list<self>
     */
    private array $otherStores;

    /**
     * Creates a new client authentication instance from the services the client used.
     */
    public function __construct(
        private ?AuthManager $auth,
        private ?Session $session,
        public ?object $authDriver = null,
        private ?SessionManager $sessions = null,
        public ?object $sessionStoreInstance = null,
    ) {
        $this->sessionDrivers = $sessions?->getDrivers() ?? [];
        $this->otherStores = $this->snapshotsOfOtherStores($session);

        $this->sessionId = $session?->getId();
        $this->sessionStarted = $session?->isStarted() ?? false;
        $this->sessionAttributes = $session?->all() ?? [];
        $this->guards = $auth instanceof AuthManager ? $this->guardsOf($auth) : [];
        $this->defaultGuard = $auth?->getDefaultDriver();
        $this->userResolver = $auth?->userResolver();
    }

    /**
     * Remembers the default guard the application configured, before a test can change it.
     */
    public static function rememberConfiguredGuard(): void
    {
        if (! function_exists('app') || ! app()->bound('config')) {
            return;
        }

        app()->instance(self::CONFIGURED_GUARD, ['guard' => config('auth.defaults.guard')]);
    }

    /**
     * Empties the session and forgets the guards, so that the next request starts as a new client.
     */
    public function isolate(): void
    {
        $this->session?->flush();
        $this->session?->migrate();
        foreach ($this->otherStores as $store) {
            $store->isolate();
        }
        $this->auth?->forgetGuards();

        if ($this->auth instanceof AuthManager && app()->bound(self::CONFIGURED_GUARD)) {
            $configured = app(self::CONFIGURED_GUARD);

            config(['auth.defaults.guard' => is_array($configured) ? ($configured['guard'] ?? null) : null]);
        }
    }

    /**
     * Puts the session and the guards of the client back.
     */
    public function restore(): void
    {
        if ($this->session instanceof Session) {
            if ($this->sessionId !== null) {
                $this->session->setId($this->sessionId);
            }

            if ($this->session instanceof Store) {
                $this->session->setExists(false);
            }

            if ($this->sessionStarted && ! $this->session->isStarted()) {
                $this->session->start();
            }

            $this->session->flush();
            $this->session->put($this->sessionAttributes);
        }

        foreach ($this->otherStores as $store) {
            $store->restore();
        }

        if ($this->sessions instanceof SessionManager) {
            $drivers = $this->sessionDrivers;

            (function () use ($drivers): void {
                $this->drivers = $drivers;
            })->call($this->sessions);
        }

        if ($this->auth instanceof AuthManager) {
            $guards = $this->guards;

            (function () use ($guards): void {
                $this->guards = $guards;
            })->call($this->auth);

            config(['auth.defaults.guard' => $this->defaultGuard]);

            if ($this->userResolver instanceof Closure) {
                $this->auth->resolveUsersUsing($this->userResolver);
            }
        }
    }

    /**
     * Returns the session stores of the client, keyed by driver, as `restore()` puts them back into the manager.
     *
     * @return array<string, mixed>
     */
    public function testSessionDrivers(): array
    {
        return $this->sessionDrivers;
    }

    /**
     * Returns the session stores of the manager that are not the stores of the client, keyed by driver: the ones a
     * request of the frontend server created or put there.
     *
     * @return array<string, mixed>
     */
    public function frontendSessionDrivers(): array
    {
        if (! $this->sessions instanceof SessionManager) {
            return [];
        }

        $snapshot = $this->sessionDrivers;

        return array_filter(
            $this->sessions->getDrivers(),
            static fn (mixed $store, string $driver): bool => ! array_key_exists($driver, $snapshot) || $snapshot[$driver] !== $store,
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * Puts the given session stores of the frontend server into the manager, each one emptied like the stores of the
     * client, so that the frontend server keeps the session its handler holds.
     *
     * @param  array<string, mixed>  $stores
     */
    public function useFrontendSessionDrivers(array $stores): void
    {
        if (! $this->sessions instanceof SessionManager || $stores === []) {
            return;
        }

        foreach ($stores as $store) {
            if ($store instanceof Session) {
                $store->flush();
                $store->migrate();
            }
        }

        (function () use ($stores): void {
            $this->drivers = array_replace($this->drivers, $stores);
        })->call($this->sessions);
    }

    /**
     * Returns a snapshot of each session store of the manager that is not the given session, each store once.
     *
     * @return list<self>
     */
    private function snapshotsOfOtherStores(?Session $session): array
    {
        $stores = [];

        foreach ($this->sessionDrivers as $store) {
            if ($store instanceof Session && $store !== $session && ! in_array($store, $stores, true)) {
                $stores[] = $store;
            }
        }

        return array_map(static fn (Session $store): self => new self(null, $store), $stores);
    }

    /**
     * Returns the guards the given manager resolved.
     *
     * @return array<array-key, mixed>
     */
    private function guardsOf(AuthManager $auth): array
    {
        return (fn (): array => $this->guards)->call($auth);
    }
}
