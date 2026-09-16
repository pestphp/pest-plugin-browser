<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Playwright\Concerns\InteractsWithPlaywright;

/**
 * @internal
 */
final readonly class CDPSession
{
    use InteractsWithPlaywright;

    /**
     * Creates a new CDP session instance.
     */
    public function __construct(
        private string $guid,
    ) {
        //
    }

    /**
     * Sends a Chrome DevTools Protocol command over this session.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function send(string $method, array $params = []): array
    {
        $response = Client::instance()->execute($this->guid, 'send', [
            'method' => $method,
            'params' => (object) $params,
        ]);

        /** @var array{result?: array{result?: array<string, mixed>}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['result'])) {
                return $message['result']['result'];
            }
        }

        return [];
    }
}
