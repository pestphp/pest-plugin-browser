<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\Device;

/**
 * @mixin PendingAwaitablePage
 */
final readonly class On
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
        private readonly array $routes,
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
        // @phpstan-ignore-next-line
        return (new PendingAwaitablePage(
            $this->browserType,
            $this->device,
            $this->url,
            $this->options,
            $this->routes,
        ))->{$name}(...$arguments);
    }

    /**
     * Sets the device to desktop.
     */
    public function desktop(): self
    {
        return new self(
            $this->browserType,
            Device::DESKTOP,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to mobile.
     */
    public function mobile(): self
    {
        return new self(
            $this->browserType,
            Device::MOBILE,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to MacBook 16".
     */
    public function macbook16(): self
    {
        return new self(
            $this->browserType,
            Device::MACBOOK_16,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to MacBook 14".
     */
    public function macbook14(): self
    {
        return new self(
            $this->browserType,
            Device::MACBOOK_14,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to MacBook Air.
     */
    public function macbookAir(): self
    {
        return new self(
            $this->browserType,
            Device::MACBOOK_AIR,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to iPhone 15 Pro.
     */
    public function iPhone15Pro(): self
    {
        return new self(
            $this->browserType,
            Device::IPHONE_15_PRO,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to iPhone 15.
     */
    public function iPhone15(): self
    {
        return new self(
            $this->browserType,
            Device::IPHONE_15,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to iPhone 14 Pro.
     */
    public function iPhone14Pro(): self
    {
        return new self(
            $this->browserType,
            Device::IPHONE_14_PRO,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to iPhone SE.
     */
    public function iPhoneSE(): self
    {
        return new self(
            $this->browserType,
            Device::IPHONE_SE,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to iPad Pro.
     */
    public function iPadPro(): self
    {
        return new self(
            $this->browserType,
            Device::IPAD_PRO,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to iPad Mini.
     */
    public function iPadMini(): self
    {
        return new self(
            $this->browserType,
            Device::IPAD_MINI,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Pixel 8.
     */
    public function pixel8(): self
    {
        return new self(
            $this->browserType,
            Device::PIXEL_8,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Pixel 7.
     */
    public function pixel7(): self
    {
        return new self(
            $this->browserType,
            Device::PIXEL_7,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Pixel 6a.
     */
    public function pixel6a(): self
    {
        return new self(
            $this->browserType,
            Device::PIXEL_6A,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Galaxy S24 Ultra.
     */
    public function galaxyS24Ultra(): self
    {
        return new self(
            $this->browserType,
            Device::GALAXY_S24_ULTRA,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Galaxy S23.
     */
    public function galaxyS23(): self
    {
        return new self(
            $this->browserType,
            Device::GALAXY_S23,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Galaxy S22.
     */
    public function galaxyS22(): self
    {
        return new self(
            $this->browserType,
            Device::GALAXY_S22,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Galaxy Note 20.
     */
    public function galaxyNote20(): self
    {
        return new self(
            $this->browserType,
            Device::GALAXY_NOTE_20,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Galaxy Tab S8.
     */
    public function galaxyTabS8(): self
    {
        return new self(
            $this->browserType,
            Device::GALAXY_TAB_S8,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Surface Pro 9.
     */
    public function surfacePro9(): self
    {
        return new self(
            $this->browserType,
            Device::SURFACE_PRO_9,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Surface Laptop 5.
     */
    public function surfaceLaptop5(): self
    {
        return new self(
            $this->browserType,
            Device::SURFACE_LAPTOP_5,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to OnePlus 11.
     */
    public function oneplus11(): self
    {
        return new self(
            $this->browserType,
            Device::ONEPLUS_11,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Xiaomi 13.
     */
    public function xiaomi13(): self
    {
        return new self(
            $this->browserType,
            Device::XIAOMI_13,
            $this->url,
            $this->options,
            $this->routes,
        );
    }

    /**
     * Sets the device to Huawei P50.
     */
    public function huaweiP50(): self
    {
        return new self(
            $this->browserType,
            Device::HUAWEI_P50,
            $this->url,
            $this->options,
            $this->routes,
        );
    }
}
