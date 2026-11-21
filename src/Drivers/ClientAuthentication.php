<?php

declare(strict_types=1);

namespace Pest\Browser\Drivers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Session\Session;

/**
 * @internal
 */
final readonly class ClientAuthentication
{
    /**
     * The identifier of the session of the client.
     */
    private ?string $sessionId;

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
     * Creates a new client authentication instance from the services the client used.
     */
    public function __construct(
        private ?AuthManager $auth,
        private ?Session $session,
    ) {
        $this->sessionId = $session?->getId();
        $this->sessionAttributes = $session?->all() ?? [];
        $this->guards = $auth instanceof AuthManager ? $this->guardsOf($auth) : [];
    }

    /**
     * Empties the session and forgets the guards, so that the next request starts as a new client.
     */
    public function isolate(): void
    {
        $this->session?->flush();
        $this->auth?->forgetGuards();
    }

    /**
     * Puts the session and the guards of the client back.
     */
    public function restore(): void
    {
        if ($this->session instanceof Session) {
            $this->session->flush();
            $this->session->put($this->sessionAttributes);

            if ($this->sessionId !== null) {
                $this->session->setId($this->sessionId);
            }
        }

        if ($this->auth instanceof AuthManager) {
            $guards = $this->guards;

            (function () use ($guards): void {
                $this->guards = $guards;
            })->call($this->auth);
        }
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
