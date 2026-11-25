<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;
use Pest\Browser\Drivers\ClientAuthentication;
use Pest\Browser\Drivers\ContainerScopedInstances;
use Pest\Browser\Enums\Device;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Playwright;

/**
 * @internal
 */
trait Browsable
{
    /**
     * Marks the test as a browser test.
     *
     * @internal
     */
    public function __markAsBrowserTest(): void
    {
        Client::instance()->connectTo(
            ServerManager::instance()->playwright()->url(),
        );

        $http = ServerManager::instance()->http();

        $http->bootstrap();
    }

    /**
     * Remembers the default guard the application configured, so that a request of the frontend server starts with it.
     *
     * @internal
     */
    public function setUpBrowsable(): void
    {
        $prepare = static function (): void {
            ClientAuthentication::rememberConfiguredGuard();
            ContainerScopedInstances::track();
        };

        if (method_exists($this, 'afterApplicationCreated')) {
            $this->afterApplicationCreated($prepare);

            return;
        }

        $prepare();
    }

    /**
     * Browse to the given URL.
     *
     * @template TUrl of array<int, string>|string
     *
     * @param  TUrl  $url
     * @param  array<string, mixed>  $options
     * @return (TUrl is array<int, string> ? ArrayablePendingAwaitablePage : PendingAwaitablePage)
     */
    public function visit(array|string $url, array $options = []): ArrayablePendingAwaitablePage|PendingAwaitablePage
    {
        if (is_string($url)) {
            return new PendingAwaitablePage(
                Playwright::defaultBrowserType(),
                Device::DESKTOP,
                $url,
                $options,
            );
        }

        return new ArrayablePendingAwaitablePage(
            array_map(fn (string $singleUrl): PendingAwaitablePage => new PendingAwaitablePage(
                Playwright::defaultBrowserType(),
                Device::DESKTOP,
                $singleUrl,
                $options,
            ), $url),
        );
    }

    /**
     * @return array <string, string>
     */
    protected function serverVariables(): array
    {
        return property_exists($this, 'serverVariables') ? $this->serverVariables : [];
    }
}
