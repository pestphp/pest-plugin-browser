<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use InvalidArgumentException;
use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\Cities;
use Pest\Browser\Enums\Device;
use Pest\Browser\Page as BrowserPage;

/**
 * @mixin PendingAwaitablePage
 */
final readonly class From
{
    /**
     * Creates a new pending awaitable page instance.
     *
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        private BrowserType $browserType,
        private Device $device,
        private string|BrowserPage $url,
        private array $options,
    ) {
        //
    }

    /**
     * Creates the actual visit page instance, and calls the given method on it.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $city = Cities::tryFrom($name);

        if (is_null($city)) {
            throw new InvalidArgumentException("City '{$name}' is not supported.");
        }

        return (new PendingAwaitablePage(
            $this->browserType,
            $this->device,
            $this->url,
            $this->options,
        ))
            ->geolocation($city->geolocation()['latitude'], $city->geolocation()['longitude'])
            ->withTimezone($city->timezone())
            ->withLocale($city->locale());
    }
}
