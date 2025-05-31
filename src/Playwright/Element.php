<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
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
        return $this->processBooleanResponse($this->sendMessage('isVisible'));
    }

    /**
     * Check if element is checked.
     */
    public function isChecked(): bool
    {
        return $this->processBooleanResponse($this->sendMessage('isChecked'));
    }

    /**
     * Check element.
     */
    public function check(): void
    {
        $this->processVoidResponse($this->sendMessage('check'));
    }

    /**
     * Uncheck element.
     */
    public function uncheck(): void
    {
        $this->processVoidResponse($this->sendMessage('uncheck'));
    }

    /**
     * Get the Attribute of the element.
     */
    public function getAttribute(string $attribute): ?string
    {
        return $this->processNullableStringResponse($this->sendMessage('getAttribute', ['name' => $attribute]));
    }

    /**
     * Click on the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function click(?array $options = null): void
    {
        $this->processVoidResponse($this->sendMessage('click', $options ?? []));
    }

    /**
     * Double click on the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function dblclick(?array $options = null): void
    {
        $this->processVoidResponse($this->sendMessage('dblclick', $options ?? []));
    }

    /**
     * Fill the element with text.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function fill(string $value, ?array $options = null): void
    {
        $params = array_merge(['value' => $value], $options ?? []);
        $this->processVoidResponse($this->sendMessage('fill', $params));
    }

    /**
     * Type text into the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function type(string $text, ?array $options = null): void
    {
        $params = array_merge(['text' => $text], $options ?? []);
        $this->processVoidResponse($this->sendMessage('type', $params));
    }

    /**
     * Press a key on the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function press(string $key, ?array $options = null): void
    {
        $params = array_merge(['key' => $key], $options ?? []);
        $this->processVoidResponse($this->sendMessage('press', $params));
    }

    /**
     * Focus the element.
     */
    public function focus(): void
    {
        $this->processVoidResponse($this->sendMessage('focus'));
    }

    /**
     * Hover over the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function hover(?array $options = null): void
    {
        $this->processVoidResponse($this->sendMessage('hover', $options ?? []));
    }

    /**
     * Select text in the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function selectText(?array $options = null): void
    {
        $this->processVoidResponse($this->sendMessage('selectText', $options ?? []));
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

        $result = $this->processArrayResponse($this->sendMessage('selectOption', $params));

        return array_map(static fn ($value): string => is_scalar($value) ? (string) $value : '', $result);
    }

    /**
     * Get the text content of the element.
     */
    public function textContent(): ?string
    {
        return $this->processNullableStringResponse($this->sendMessage('textContent'));
    }

    /**
     * Get the inner text of the element.
     */
    public function innerText(): string
    {
        return $this->processStringResponse($this->sendMessage('innerText'));
    }

    /**
     * Get the inner HTML of the element.
     */
    public function innerHTML(): string
    {
        return $this->processStringResponse($this->sendMessage('innerHTML'));
    }

    /**
     * Get the input value of the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function inputValue(?array $options = null): string
    {
        return $this->processStringResponse($this->sendMessage('inputValue', $options ?? []));
    }

    /**
     * Check if element is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->processBooleanResponse($this->sendMessage('isEnabled'));
    }

    /**
     * Check if element is disabled.
     */
    public function isDisabled(): bool
    {
        return $this->processBooleanResponse($this->sendMessage('isDisabled'));
    }

    /**
     * Check if element is editable.
     */
    public function isEditable(): bool
    {
        return $this->processBooleanResponse($this->sendMessage('isEditable'));
    }

    /**
     * Check if element is hidden.
     */
    public function isHidden(): bool
    {
        return $this->processBooleanResponse($this->sendMessage('isHidden'));
    }

    /**
     * Get the bounding box of the element.
     *
     * @return array{x: float, y: float, width: float, height: float}|null
     */
    public function boundingBox(): ?array
    {
        $result = $this->processResultResponse($this->sendMessage('boundingBox'));

        if ($result === null) {
            return null;
        }

        if (! is_array($result) || ! isset($result['x'], $result['y'], $result['width'], $result['height'])) {
            return null;
        }

        return [
            'x' => is_numeric($result['x']) ? (float) $result['x'] : 0.0,
            'y' => is_numeric($result['y']) ? (float) $result['y'] : 0.0,
            'width' => is_numeric($result['width']) ? (float) $result['width'] : 0.0,
            'height' => is_numeric($result['height']) ? (float) $result['height'] : 0.0,
        ];
    }

    /**
     * Take a screenshot of the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function screenshot(?array $options = null): string
    {
        return $this->processStringResponse($this->sendMessage('screenshot', $options ?? []));
    }

    /**
     * Scroll element into view if needed.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function scrollIntoViewIfNeeded(?array $options = null): void
    {
        $this->processVoidResponse($this->sendMessage('scrollIntoViewIfNeeded', $options ?? []));
    }

    /**
     * Wait for element to reach a specific state.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitForElementState(string $state, ?array $options = null): void
    {
        $params = array_merge(['state' => $state], $options ?? []);
        $this->processVoidResponse($this->sendMessage('waitForElementState', $params));
    }

    /**
     * Wait for a selector to appear relative to this element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitForSelector(string $selector, ?array $options = null): ?self
    {
        $params = array_merge(['selector' => $selector], $options ?? []);

        return $this->processElementResponse($this->sendMessage('waitForSelector', $params));
    }

    /**
     * Query for a single element relative to this element.
     */
    public function querySelector(string $selector): ?self
    {
        return $this->processElementCreationResponse($this->sendMessage('querySelector', ['selector' => $selector]));
    }

    /**
     * Query for multiple elements relative to this element.
     *
     * @return array<self>
     */
    public function querySelectorAll(string $selector): array
    {
        return $this->processMultipleElementCreationResponse($this->sendMessage('querySelectorAll', ['selector' => $selector]));
    }

    /**
     * Get the content frame for iframe elements.
     */
    public function contentFrame(): ?object
    {
        $result = $this->processResultResponse($this->sendMessage('contentFrame'));

        return $result !== null ? (object) ['guid' => $result] : null;
    }

    /**
     * Get the owner frame of the element.
     */
    public function ownerFrame(): ?object
    {
        $result = $this->processResultResponse($this->sendMessage('ownerFrame'));

        return $result !== null ? (object) ['guid' => $result] : null;
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

    /**
     * Send a message to the element through the Client.
     *
     * @param  array<string, mixed>  $params
     */
    private function sendMessage(string $method, array $params = []): Generator
    {
        return Client::instance()->execute($this->guid, $method, $params);
    }

    /**
     * Process response and return result value.
     */
    private function processResultResponse(Generator $response): mixed
    {
        /** @var array{result: array{value: mixed}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return null;
    }

    /**
     * Process response and return string value.
     */
    private function processStringResponse(Generator $response): string
    {
        $result = $this->processResultResponse($response);

        if (! is_string($result) && ! is_numeric($result)) {
            return '';
        }

        return (string) $result;
    }

    /**
     * Process response and return nullable string value.
     */
    private function processNullableStringResponse(Generator $response): ?string
    {
        $result = $this->processResultResponse($response);

        if ($result === null) {
            return null;
        }

        if (! is_string($result) && ! is_numeric($result)) {
            return null;
        }

        return (string) $result;
    }

    /**
     * Process response and return boolean value.
     */
    private function processBooleanResponse(Generator $response): bool
    {
        $result = $this->processResultResponse($response);

        return (bool) ($result ?? false);
    }

    /**
     * Process response and return array value.
     *
     * @return array<mixed>
     */
    private function processArrayResponse(Generator $response): array
    {
        $result = $this->processResultResponse($response);

        return (array) ($result ?? []);
    }

    /**
     * Process response for void methods (consume all messages).
     */
    private function processVoidResponse(Generator $response): void
    {
        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Process response to return Element instance from result value.
     */
    private function processElementResponse(Generator $response): ?self
    {
        $result = $this->processResultResponse($response);

        if (! is_string($result)) {
            return null;
        }

        return new self($result);
    }

    /**
     * Process response to handle element creation messages.
     */
    private function processElementCreationResponse(Generator $response): ?self
    {
        /** @var array{method: string|null, params: array{type: string|null, guid: string}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method'], $message['params']['type'], $message['params']['guid'])
                && $message['method'] === '__create__'
                && $message['params']['type'] === 'ElementHandle'
            ) {
                return new self($message['params']['guid']);
            }
        }

        return null;
    }

    /**
     * Process response to handle multiple element creation messages.
     *
     * @return array<self>
     */
    private function processMultipleElementCreationResponse(Generator $response): array
    {
        $elements = [];

        /** @var array{method: string|null, params: array{type: string|null, guid: string}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method'], $message['params']['type'], $message['params']['guid'])
                && $message['method'] === '__create__'
                && $message['params']['type'] === 'ElementHandle'
            ) {
                $elements[] = new self($message['params']['guid']);
            }
        }

        return $elements;
    }
}
