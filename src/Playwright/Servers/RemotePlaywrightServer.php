<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright\Servers;

use InvalidArgumentException;
use Pest\Browser\Contracts\PlaywrightServer;

/**
 * @internal
 */
final readonly class RemotePlaywrightServer implements PlaywrightServer
{
    public function __construct(private string $endpoint)
    {
        $parts = parse_url($endpoint);

        if ($parts === false || ! in_array($parts['scheme'] ?? null, ['ws', 'wss'], true) || ($parts['host'] ?? '') === '' || isset($parts['fragment'])) {
            throw new InvalidArgumentException('The Playwright endpoint must be a ws:// or wss:// URL without a fragment.');
        }
    }

    public function start(): void
    {
        AlreadyStartedPlaywrightServer::persist('', 0, $this->endpoint);
    }

    public function stop(): void
    {
        // 
    }

    public function url(): string
    {
        return $this->endpoint;
    }
}
