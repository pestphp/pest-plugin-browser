<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Playwright\CDPSession;

final readonly class VirtualAuthenticator
{
    /**
     * Creates a new virtual authenticator instance.
     */
    public function __construct(
        private CDPSession $session,
        private string $id,
    ) {
        //
    }

    /**
     * Gets the credentials registered on this virtual authenticator.
     *
     * @return array<int, array<string, mixed>>
     */
    public function credentials(): array
    {
        $result = $this->session->send('WebAuthn.getCredentials', [
            'authenticatorId' => $this->id,
        ]);

        /** @var array<int, array<string, mixed>> $credentials */
        $credentials = $result['credentials'] ?? [];

        return $credentials;
    }
}
