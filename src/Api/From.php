<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\City;
use Pest\Browser\Enums\Device;

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
        private string $url,
        private array $options,
    ) {
        //
    }

    /**
     * Sets the city to Amsterdam.
     */
    public function amsterdam(): PendingAwaitablePage
    {
        return $this->city(City::AMSTERDAM);
    }

    /**
     * Sets the city to Berlin.
     */
    public function berlin(): PendingAwaitablePage
    {
        return $this->city(City::BERLIN);
    }

    /**
     * Sets the city to Chicago.
     */
    public function chicago(): PendingAwaitablePage
    {
        return $this->city(City::CHICAGO);
    }

    /**
     * Sets the city to Houston.
     */
    public function houston(): PendingAwaitablePage
    {
        return $this->city(City::HOUSTON);
    }

    /**
     * Sets the city to London.
     */
    public function london(): PendingAwaitablePage
    {
        return $this->city(City::LONDON);
    }

    /**
     * Sets the city to Los Angeles.
     */
    public function losAngeles(): PendingAwaitablePage
    {
        return $this->city(City::LOS_ANGELES);
    }

    /**
     * Sets the city to Miami.
     */
    public function miami(): PendingAwaitablePage
    {
        return $this->city(City::MIAMI);
    }

    /**
     * Sets the city to New York.
     */
    public function newYork(): PendingAwaitablePage
    {
        return $this->city(City::NEW_YORK);
    }

    /**
     * Sets the city to Paris.
     */
    public function paris(): PendingAwaitablePage
    {
        return $this->city(City::PARIS);
    }

    /**
     * Sets the city to Tokyo.
     */
    public function tokyo(): PendingAwaitablePage
    {
        return $this->city(City::TOKYO);
    }

    /**
     * Sets the city to Toronto.
     */
    public function toronto(): PendingAwaitablePage
    {
        return $this->city(City::TORONTO);
    }

    /**
     * Sets the city to San Francisco.
     */
    public function sanFrancisco(): PendingAwaitablePage
    {
        return $this->city(City::SAN_FRANCISCO);
    }

    /**
     * Sets the city to Sydney.
     */
    public function sydney(): PendingAwaitablePage
    {
        return $this->city(City::SYDNEY);
    }

    /**
     * Creates the actual visit page instance using the provided city.
     */
    private function city(City $city): PendingAwaitablePage
    {
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
