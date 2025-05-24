<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Support\Selector;

/**
 * @internal
 */
final class Element
{
    /**
     * Constructs new element
     */
    public function __construct(
        public string $guid,
    ) {
        //
    }

    /**
     * Check if element is visible.
     */
    public function isVisible(): bool
    {
        $response = Client::instance()->execute($this->guid, 'isVisible');

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check if element is checked.
     */
    public function isChecked(): bool
    {
        $response = Client::instance()->execute($this->guid, 'isChecked');

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check element.
     */
    public function check(): void
    {
        $response = Client::instance()->execute($this->guid, 'check');

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Uncheck element.
     */
    public function uncheck(): void
    {
        $response = Client::instance()->execute($this->guid, 'uncheck');

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Get the Attribute of the element.
     */
    public function getAttribute(string $attribute): ?string
    {
        $response = Client::instance()->execute($this->guid, 'getAttribute', ['name' => $attribute]);

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return null;
    }

    /**
     * Click on the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function click(?array $options = null): void
    {
        $params = $options ?? [];
        $response = Client::instance()->execute($this->guid, 'click', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Double click on the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function dblclick(?array $options = null): void
    {
        $params = $options ?? [];
        $response = Client::instance()->execute($this->guid, 'dblclick', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Fill the element with text.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function fill(string $value, ?array $options = null): void
    {
        $params = array_merge(['value' => $value], $options ?? []);
        $response = Client::instance()->execute($this->guid, 'fill', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Type text into the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function type(string $text, ?array $options = null): void
    {
        $params = array_merge(['text' => $text], $options ?? []);
        $response = Client::instance()->execute($this->guid, 'type', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Press a key on the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function press(string $key, ?array $options = null): void
    {
        $params = array_merge(['key' => $key], $options ?? []);
        $response = Client::instance()->execute($this->guid, 'press', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Focus the element.
     */
    public function focus(): void
    {
        $response = Client::instance()->execute($this->guid, 'focus');

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Hover over the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function hover(?array $options = null): void
    {
        $params = $options ?? [];
        $response = Client::instance()->execute($this->guid, 'hover', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Select text in the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function selectText(?array $options = null): void
    {
        $params = $options ?? [];
        $response = Client::instance()->execute($this->guid, 'selectText', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Select options in a select element.
     *
     * @param  string|array<string>|array<int, string>  $values
     * @param  array<string, mixed>|null  $options
     * @return array<string>
     */
    public function selectOption(string|array $values, ?array $options = null): array
    {
        $params = array_merge(['values' => $values], $options ?? []);
        $response = Client::instance()->execute($this->guid, 'selectOption', $params);

        /** @var array{result: array{value: array<string>}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return [];
    }

    /**
     * Get the text content of the element.
     */
    public function textContent(): ?string
    {
        $response = Client::instance()->execute($this->guid, 'textContent');

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return null;
    }

    /**
     * Get the inner text of the element.
     */
    public function innerText(): string
    {
        $response = Client::instance()->execute($this->guid, 'innerText');

        /** @var array{result: array{value: string}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return '';
    }

    /**
     * Get the inner HTML of the element.
     */
    public function innerHTML(): string
    {
        $response = Client::instance()->execute($this->guid, 'innerHTML');

        /** @var array{result: array{value: string}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return '';
    }

    /**
     * Get the input value of the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function inputValue(?array $options = null): string
    {
        $params = $options ?? [];
        $response = Client::instance()->execute($this->guid, 'inputValue', $params);

        /** @var array{result: array{value: string}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return '';
    }

    /**
     * Check if element is enabled.
     */
    public function isEnabled(): bool
    {
        $response = Client::instance()->execute($this->guid, 'isEnabled');

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check if element is disabled.
     */
    public function isDisabled(): bool
    {
        $response = Client::instance()->execute($this->guid, 'isDisabled');

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check if element is editable.
     */
    public function isEditable(): bool
    {
        $response = Client::instance()->execute($this->guid, 'isEditable');

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check if element is hidden.
     */
    public function isHidden(): bool
    {
        $response = Client::instance()->execute($this->guid, 'isHidden');

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Get the bounding box of the element.
     *
     * @return array{x: float, y: float, width: float, height: float}|null
     */
    public function boundingBox(): ?array
    {
        $response = Client::instance()->execute($this->guid, 'boundingBox');

        /** @var array{result: array{value: array{x: float, y: float, width: float, height: float}|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return null;
    }

    /**
     * Take a screenshot of the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function screenshot(?array $options = null): string
    {
        $params = $options ?? [];
        $response = Client::instance()->execute($this->guid, 'screenshot', $params);

        /** @var array{result: array{value: string}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return '';
    }

    /**
     * Scroll element into view if needed.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function scrollIntoViewIfNeeded(?array $options = null): void
    {
        $params = $options ?? [];
        $response = Client::instance()->execute($this->guid, 'scrollIntoViewIfNeeded', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Wait for element to reach a specific state.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitForElementState(string $state, ?array $options = null): void
    {
        $params = array_merge(['state' => $state], $options ?? []);
        $response = Client::instance()->execute($this->guid, 'waitForElementState', $params);

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Wait for a selector to appear relative to this element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitForSelector(string $selector, ?array $options = null): ?self
    {
        $params = array_merge(['selector' => $selector], $options ?? []);
        $response = Client::instance()->execute($this->guid, 'waitForSelector', $params);

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value']) && $message['result']['value'] !== null) {
                return new self($message['result']['value']);
            }
        }

        return null;
    }

    /**
     * Query for a single element relative to this element.
     */
    public function querySelector(string $selector): ?self
    {
        $response = Client::instance()->execute($this->guid, 'querySelector', ['selector' => $selector]);

        /** @var array{method: string|null, params: array{type: string|null, guid: string}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method']) && $message['method'] === '__create__'
                && isset($message['params']['type']) && $message['params']['type'] === 'ElementHandle'
            ) {
                return new self($message['params']['guid']);
            }
        }

        return null;
    }

    /**
     * Query for multiple elements relative to this element.
     *
     * @return array<self>
     */
    public function querySelectorAll(string $selector): array
    {
        $response = Client::instance()->execute($this->guid, 'querySelectorAll', ['selector' => $selector]);

        /** @var array{method: string|null, params: array{type: string|null, guid: string}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method']) && $message['method'] === '__create__'
                && isset($message['params']['type']) && $message['params']['type'] === 'ElementHandle'
            ) {
                return [new self($message['params']['guid'])];
            }
        }

        return [];
    }

    /**
     * Get the content frame for iframe elements.
     */
    public function contentFrame(): ?object
    {
        $response = Client::instance()->execute($this->guid, 'contentFrame');

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value']) && $message['result']['value'] !== null) {
                // Return frame object - this would need Frame class implementation
                return (object) ['guid' => $message['result']['value']];
            }
        }

        return null;
    }

    /**
     * Get the owner frame of the element.
     */
    public function ownerFrame(): ?object
    {
        $response = Client::instance()->execute($this->guid, 'ownerFrame');

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value']) && $message['result']['value'] !== null) {
                // Return frame object - this would need Frame class implementation
                return (object) ['guid' => $message['result']['value']];
            }
        }

        return null;
    }

    /**
     * Get element by role relative to this element.
     *
     * @param  array<string, string|bool>  $options
     */
    public function getByRole(string $role, array $options = []): ?self
    {
        return $this->querySelector(Selector::getByRoleSelector($role, $options));
    }

    /**
     * Get element by test ID relative to this element.
     */
    public function getByTestId(string $testId): ?self
    {
        $testIdAttributeName = 'data-testid';

        return $this->querySelector(Selector::getByTestIdSelector($testIdAttributeName, $testId));
    }

    /**
     * Get element by text relative to this element.
     */
    public function getByText(string $text, bool $exact = false): ?self
    {
        return $this->querySelector(Selector::getByTextSelector($text, $exact));
    }

    /**
     * Get element by label relative to this element.
     */
    public function getByLabel(string $text, bool $exact = false): ?self
    {
        return $this->querySelector(Selector::getByLabelSelector($text, $exact));
    }

    /**
     * Get element by placeholder relative to this element.
     */
    public function getByPlaceholder(string $text, bool $exact = false): ?self
    {
        return $this->querySelector(Selector::getByPlaceholderSelector($text, $exact));
    }

    /**
     * Get element by alt text relative to this element.
     */
    public function getByAltText(string $text, bool $exact = false): ?self
    {
        return $this->querySelector(Selector::getByAltTextSelector($text, $exact));
    }

    /**
     * Get element by title relative to this element.
     */
    public function getByTitle(string $text, bool $exact = false): ?self
    {
        return $this->querySelector(Selector::getByTitleSelector($text, $exact));
    }
}
