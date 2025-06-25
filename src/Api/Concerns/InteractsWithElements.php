<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;
use Pest\Browser\Execution;

/**
 * @mixin Webpage
 */
trait InteractsWithElements
{
    /**
     * Click the link with the given text.
     */
    public function click(string $text): Webpage
    {
        $this->guessLocator($text)->click();

        return $this;
    }

    /**
     * Get the text of the element matching the given selector.
     */
    public function text(string $selector): ?string
    {
        return $this->guessLocator($selector)->textContent();
    }

    /**
     * Get the given attribute from the element matching the given selector.
     */
    public function attribute(string $selector, string $attribute): ?string
    {
        $locator = $this->guessLocator($selector);

        return $locator->getAttribute($attribute) ?? null;
    }

    /**
     * Send the given keys to the element matching the given selector.
     *
     * @param  array<int, string>  $keys
     */
    public function keys(string $selector, array|string $keys): Webpage
    {
        $keys = is_array($keys) ? $keys : [$keys];

        $locator = $this->guessLocator($selector);

        foreach ($keys as $key) {
            $locator->press($key);
        }

        return $this;
    }

    /**
     * Type the given value in the given field.
     */
    public function type(string $field, string $value): Webpage
    {
        $this->guessLocator($field)->fill($value);

        return $this;
    }

    /**
     * Type the given value in the given field without clearing it.
     */
    public function append(string $field, string $value): Webpage
    {
        $locator = $this->guessLocator($field);

        $currentValue = $locator->inputValue();

        $locator->fill($currentValue.$value);

        return $this;
    }

    /**
     * Clear the given field.
     */
    public function clear(string $field): Webpage
    {
        $this->guessLocator($field)->clear();

        return $this;
    }

    /**
     * Select the given value of a radio button field.
     */
    public function radio(string $field, string $value): Webpage
    {
        $this->guessLocator($field, $value)->click();

        return $this;
    }

    /**
     * Check the given checkbox.
     */
    public function check(string $field, ?string $value = null): Webpage
    {
        $this->guessLocator($field, $value)->check();

        return $this;
    }

    /**
     * Uncheck the given checkbox.
     */
    public function uncheck(string $field, ?string $value = null): Webpage
    {
        $this->guessLocator($field, $value)->uncheck();

        return $this;
    }

    /**
     * Attach the given file to the field.
     */
    public function attach(string $field, string $path): Webpage
    {
        $this->guessLocator($field)->setInputFiles($path);

        return $this;
    }

    /**
     * Press the button with the given text or name.
     */
    public function press(string $button): Webpage
    {
        $this->guessLocator($button)->click();

        return $this;
    }

    /**
     * Press the button with the given text or name.
     */
    public function pressAndWaitFor(string $button, int|float $seconds = 1): Webpage
    {
        $locator = $this->guessLocator($button);
        $locator->click();

        $this->pause($seconds);

        return $this;
    }

    /**
     * Drag an element to another element using selectors.
     */
    public function drag(string $from, string $to): Webpage
    {
        $fromLocator = $this->guessLocator($from);
        $toLocator = $this->guessLocator($to);

        $fromLocator->dragTo($toLocator);

        return $this;
    }

    /**
     * Pause for the given number of seconds.
     */
    public function pause(int|float $seconds): Webpage
    {
        Execution::instance()->pause($seconds);

        return $this;
    }
}
