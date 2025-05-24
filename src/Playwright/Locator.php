<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Support\Selector;

/**
 * @internal
 */
final class Locator
{
    /**
     * Constructs new locator
     */
    public function __construct(
        public string $frameGuid,
        public string $selector,
    ) {
        //
    }

    /**
     * Check if element matching the locator is visible.
     */
    public function isVisible(): bool
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'isVisible',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check if element matching the locator is checked.
     */
    public function isChecked(): bool
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'isChecked',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check if element matching the locator is enabled.
     */
    public function isEnabled(): bool
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'isEnabled',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check if element matching the locator is disabled.
     */
    public function isDisabled(): bool
    {
        return ! $this->isEnabled();
    }

    /**
     * Check if element matching the locator is hidden.
     */
    public function isHidden(): bool
    {
        return ! $this->isVisible();
    }

    /**
     * Check if element matching the locator is editable.
     */
    public function isEditable(): bool
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'isEditable',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check element matching the locator.
     */
    public function check(): void
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'check',
            ['selector' => $this->selector, 'strict' => true]
        );

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Uncheck element matching the locator.
     */
    public function uncheck(): void
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'uncheck',
            ['selector' => $this->selector, 'strict' => true]
        );

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Click on the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function click(?array $options = null): void
    {
        $params = array_merge(['selector' => $this->selector, 'strict' => true], $options ?? []);
        $response = Client::instance()->execute($this->frameGuid, 'click', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Double click on the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function dblclick(?array $options = null): void
    {
        $params = array_merge(['selector' => $this->selector, 'strict' => true], $options ?? []);
        $response = Client::instance()->execute($this->frameGuid, 'dblclick', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Fill the element matching the locator with text.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function fill(string $value, ?array $options = null): void
    {
        $params = array_merge(['selector' => $this->selector, 'value' => $value, 'strict' => true], $options ?? []);
        $response = Client::instance()->execute($this->frameGuid, 'fill', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Type text into the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function type(string $text, ?array $options = null): void
    {
        $params = array_merge(['selector' => $this->selector, 'text' => $text, 'strict' => true], $options ?? []);
        $response = Client::instance()->execute($this->frameGuid, 'type', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Clear the element matching the locator.
     */
    public function clear(): void
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'fill',
            ['selector' => $this->selector, 'value' => '', 'strict' => true]
        );

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Focus the element matching the locator.
     */
    public function focus(): void
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'focus',
            ['selector' => $this->selector, 'strict' => true]
        );

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Hover over the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function hover(?array $options = null): void
    {
        $params = array_merge(['selector' => $this->selector, 'strict' => true], $options ?? []);
        $response = Client::instance()->execute($this->frameGuid, 'hover', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Press a key on the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function press(string $key, ?array $options = null): void
    {
        $params = array_merge(['selector' => $this->selector, 'key' => $key, 'strict' => true], $options ?? []);
        $response = Client::instance()->execute($this->frameGuid, 'press', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Select options by value in a select element matching the locator.
     *
     * @param  string|array<string>  $values
     * @param  array<string, mixed>|null  $options
     */
    public function selectOption($values, ?array $options = null): void
    {
        $values = is_array($values) ? $values : [$values];
        $params = array_merge(['selector' => $this->selector, 'values' => $values, 'strict' => true], $options ?? []);
        $response = Client::instance()->execute($this->frameGuid, 'selectOption', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Get the text content of the element matching the locator.
     */
    public function textContent(): ?string
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'textContent',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return null;
    }

    /**
     * Get the inner text of the element matching the locator.
     */
    public function innerText(): string
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'innerText',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'] ?? '';
            }
        }

        return '';
    }

    /**
     * Get the inner HTML of the element matching the locator.
     */
    public function innerHTML(): string
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'innerHTML',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'] ?? '';
            }
        }

        return '';
    }

    /**
     * Get the value of an input element matching the locator.
     */
    public function inputValue(): string
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'inputValue',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'] ?? '';
            }
        }

        return '';
    }

    /**
     * Get an attribute value of the element matching the locator.
     */
    public function getAttribute(string $name): ?string
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'getAttribute',
            ['selector' => $this->selector, 'name' => $name, 'strict' => true]
        );

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return null;
    }

    /**
     * Wait for the element matching the locator to be in a specific state.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitFor(?array $options = null): void
    {
        $params = array_merge(['selector' => $this->selector, 'strict' => true], $options ?? []);
        $response = Client::instance()->execute($this->frameGuid, 'waitForSelector', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Create a locator for the first element matching the selector.
     */
    public function first(): self
    {
        return new self($this->frameGuid, $this->selector.' >> nth=0');
    }

    /**
     * Create a locator for the last element matching the selector.
     */
    public function last(): self
    {
        return new self($this->frameGuid, $this->selector.' >> nth=-1');
    }

    /**
     * Create a locator for the nth element matching the selector.
     */
    public function nth(int $index): self
    {
        return new self($this->frameGuid, $this->selector." >> nth={$index}");
    }

    /**
     * Create a locator that matches elements containing the specified text.
     */
    public function getByText(string $text, bool $exact = false): self
    {
        $textSelector = Selector::getByTextSelector($text, $exact);

        return new self($this->frameGuid, $this->selector.' >> '.$textSelector);
    }

    /**
     * Create a locator that matches elements with the specified role.
     *
     * @param  array<string, string|bool>  $params
     */
    public function getByRole(string $role, array $params = []): self
    {
        $roleSelector = Selector::getByRoleSelector($role, $params);

        return new self($this->frameGuid, $this->selector.' >> '.$roleSelector);
    }

    /**
     * Create a locator that matches elements with the specified test ID.
     */
    public function getByTestId(string $testId): self
    {
        $testIdSelector = Selector::getByTestIdSelector('data-testid', $testId);

        return new self($this->frameGuid, $this->selector.' >> '.$testIdSelector);
    }

    /**
     * Create a locator that matches elements with the specified alt text.
     */
    public function getByAltText(string $text, bool $exact = false): self
    {
        $altTextSelector = Selector::getByAltTextSelector($text, $exact);

        return new self($this->frameGuid, $this->selector.' >> '.$altTextSelector);
    }

    /**
     * Create a locator that matches elements with the specified label.
     */
    public function getByLabel(string $text, bool $exact = false): self
    {
        $labelSelector = Selector::getByLabelSelector($text, $exact);

        return new self($this->frameGuid, $this->selector.' >> '.$labelSelector);
    }

    /**
     * Create a locator that matches elements with the specified placeholder.
     */
    public function getByPlaceholder(string $text, bool $exact = false): self
    {
        $placeholderSelector = Selector::getByPlaceholderSelector($text, $exact);

        return new self($this->frameGuid, $this->selector.' >> '.$placeholderSelector);
    }

    /**
     * Create a locator that matches elements with the specified title.
     */
    public function getByTitle(string $text, bool $exact = false): self
    {
        $titleSelector = Selector::getByTitleSelector($text, $exact);

        return new self($this->frameGuid, $this->selector.' >> '.$titleSelector);
    }

    /**
     * Create a locator using a CSS selector or other selector relative to this locator.
     */
    public function locator(string $selector): self
    {
        return new self($this->frameGuid, $this->selector.' >> '.$selector);
    }

    /**
     * Filter this locator to only match elements that also match the given locator or predicate.
     */
    public function filter(string $selector): self
    {
        return new self($this->frameGuid, $this->selector.':has('.$selector.')');
    }

    /**
     * Count the number of elements matching this locator.
     */
    public function count(): int
    {
        // Use the nth selector approach to count elements
        $count = 0;

        // Try up to 100 elements (reasonable limit)
        for ($i = 0; $i < 100; $i++) {
            $nthSelector = $this->selector." >> nth={$i}";

            $response = Client::instance()->execute(
                $this->frameGuid,
                'querySelector',
                ['selector' => $nthSelector]
            );

            $found = false;
            foreach ($response as $message) {
                if (
                    isset($message['method']) && $message['method'] === '__create__'
                    && isset($message['params']['type']) && $message['params']['type'] === 'ElementHandle'
                ) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                break;
            }

            $count++;
        }

        return $count;
    }

    /**
     * Get the Element handle for this locator.
     * Returns the first element matching the locator.
     */
    public function elementHandle(): ?Element
    {
        $response = Client::instance()->execute(
            $this->frameGuid,
            'querySelector',
            ['selector' => $this->selector, 'strict' => true]
        );

        /** @var array{method: string|null, params: array{type: string|null, guid: string}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method']) && $message['method'] === '__create__'
                && isset($message['params']['type']) && $message['params']['type'] === 'ElementHandle'
            ) {
                return new Element($message['params']['guid']);
            }
        }

        return null;
    }
}
