<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;

/**
 * @mixin Webpage
 */
trait InteractsWithElements
{
    /**
     * Click the link with the given text.
     */
    public function click(string $text): self
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
    public function keys(string $selector, array|string $keys): self
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
    public function type(string $field, string $value): self
    {
        $this->guessLocator($field)->fill($value);

        return $this;
    }

    /**
     * Type the given value slowly in the given field.
     */
    public function typeSlowly(string $field, string $value, int $delay = 100): self
    {
        $options = ['delay' => $delay];

        $this->guessLocator($field)->type($value, $options);

        return $this;
    }

    /**
     * Fills the given value in the given field.
     */
    public function fill(string $field, string $value): self
    {
        return $this->type($field, $value);
    }

    /**
     * Hovers over the element matching the given selector.
     */
    public function hover(string $selector): self
    {
        $this->guessLocator($selector)->hover();

        return $this;
    }

    /**
     * Right-click the element matching the given selector.
     */
    public function rightClick(string $text): Webpage
    {
        $this->guessLocator($text)->click([
            'button' => 'right',
        ]);

        return $this;
    }

    /**
     * Select the given value in the given field.
     *
     * @param  array<int, string|int>|string|int  $option
     */
    public function select(string $field, array|string|int $option): self
    {
        $this->guessLocator($field)->selectOption($option);

        return $this;
    }

    /**
     * Type the given value in the given field without clearing it.
     */
    public function append(string $field, string $value): self
    {
        $locator = $this->guessLocator($field);

        $currentValue = $locator->inputValue();

        $locator->fill($currentValue.$value);

        return $this;
    }

    /**
     * Clear the given field.
     */
    public function clear(string $field): self
    {
        $this->guessLocator($field)->clear();

        return $this;
    }

    /**
     * Select the given value of a radio button field.
     */
    public function radio(string $field, string $value): self
    {
        $this->guessLocator($field, $value)->click();

        return $this;
    }

    /**
     * Check the given checkbox.
     */
    public function check(string $field, ?string $value = null): self
    {
        $this->guessLocator($field, $value)->check();

        return $this;
    }

    /**
     * Uncheck the given checkbox.
     */
    public function uncheck(string $field, ?string $value = null): self
    {
        $this->guessLocator($field, $value)->uncheck();

        return $this;
    }

    /**
     * Attach the given file to the field.
     */
    public function attach(string $field, string $path): self
    {
        $this->guessLocator($field)->setInputFiles($path);

        return $this;
    }

    /**
     * Press the button with the given text or name.
     */
    public function press(string $button): self
    {
        return $this->click($button);
    }

    /**
     * Press the button with the given text or name.
     */
    public function pressAndWaitFor(string $button, int|float $seconds = 1): self
    {
        return $this->press($button)->wait($seconds);
    }

    /**
     * Drag an element to another element using selectors.
     */
    public function drag(string $from, string $to): self
    {
        $fromLocator = $this->guessLocator($from);
        $toLocator = $this->guessLocator($to);

        $fromLocator->dragTo($toLocator);

        return $this;
    }

    /**
     * Hold down key while running callback
     */
    public function withKeyDown(string $key, callable $callback): self
    {
        $this->page->keyDown($key);

        try {
            $callback($this);
        } finally {
            $this->page->keyUp($key);
        }

        return $this;
    }
}
