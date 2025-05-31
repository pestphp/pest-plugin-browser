<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
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
        $response = $this->sendMessage('isVisible');

        return $this->processBooleanResponse($response);
    }

    /**
     * Check if element matching the locator is checked.
     */
    public function isChecked(): bool
    {
        $response = $this->sendMessage('isChecked');

        return $this->processBooleanResponse($response);
    }

    /**
     * Check if element matching the locator is enabled.
     */
    public function isEnabled(): bool
    {
        $response = $this->sendMessage('isEnabled');

        return $this->processBooleanResponse($response);
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
        $response = $this->sendMessage('isEditable');

        return $this->processBooleanResponse($response);
    }

    /**
     * Check element matching the locator.
     */
    public function check(): void
    {
        $response = $this->sendMessage('check');
        $this->processVoidResponse($response);
    }

    /**
     * Uncheck element matching the locator.
     */
    public function uncheck(): void
    {
        $response = $this->sendMessage('uncheck');
        $this->processVoidResponse($response);
    }

    /**
     * Click on the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function click(?array $options = null): void
    {
        $response = $this->sendMessage('click', $options ?? []);
        $this->processVoidResponse($response);
    }

    /**
     * Double click on the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function dblclick(?array $options = null): void
    {
        $response = $this->sendMessage('dblclick', $options ?? []);
        $this->processVoidResponse($response);
    }

    /**
     * Fill the element matching the locator with text.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function fill(string $value, ?array $options = null): void
    {
        $params = array_merge(['value' => $value], $options ?? []);
        $response = $this->sendMessage('fill', $params);
        $this->processVoidResponse($response);
    }

    /**
     * Type text into the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function type(string $text, ?array $options = null): void
    {
        $params = array_merge(['text' => $text], $options ?? []);
        $response = $this->sendMessage('type', $params);
        $this->processVoidResponse($response);
    }

    /**
     * Clear the element matching the locator.
     */
    public function clear(): void
    {
        $response = $this->sendMessage('fill', ['value' => '']);
        $this->processVoidResponse($response);
    }

    /**
     * Focus the element matching the locator.
     */
    public function focus(): void
    {
        $response = $this->sendMessage('focus');
        $this->processVoidResponse($response);
    }

    /**
     * Hover over the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function hover(?array $options = null): void
    {
        $response = $this->sendMessage('hover', $options ?? []);
        $this->processVoidResponse($response);
    }

    /**
     * Press a key on the element matching the locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function press(string $key, ?array $options = null): void
    {
        $params = array_merge(['key' => $key], $options ?? []);
        $response = $this->sendMessage('press', $params);
        $this->processVoidResponse($response);
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
        $params = array_merge(['values' => $values], $options ?? []);
        $response = $this->sendMessage('selectOption', $params);
        $this->processVoidResponse($response);
    }

    /**
     * Get the text content of the element matching the locator.
     */
    public function textContent(): ?string
    {
        $response = $this->sendMessage('textContent');

        return $this->processNullableStringResponse($response);
    }

    /**
     * Get the inner text of the element matching the locator.
     */
    public function innerText(): string
    {
        $response = $this->sendMessage('innerText');

        return $this->processStringResponse($response);
    }

    /**
     * Get the inner HTML of the element matching the locator.
     */
    public function innerHTML(): string
    {
        $response = $this->sendMessage('innerHTML');

        return $this->processStringResponse($response);
    }

    /**
     * Get the value of an input element matching the locator.
     */
    public function inputValue(): string
    {
        $response = $this->sendMessage('inputValue');

        return $this->processStringResponse($response);
    }

    /**
     * Get an attribute value of the element matching the locator.
     */
    public function getAttribute(string $name): ?string
    {
        $response = $this->sendMessage('getAttribute', ['name' => $name]);

        return $this->processNullableStringResponse($response);
    }

    /**
     * Wait for the element matching the locator to be in a specific state.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitFor(?array $options = null): void
    {
        $response = $this->sendMessage('waitForSelector', $options ?? []);
        $this->processVoidResponse($response);
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
            /** @var array<string, mixed> $message */
            foreach ($response as $message) {
                if (
                    isset($message['method'], $message['params']['type'])
                    && $message['method'] === '__create__'
                    && isset($message['params']) && is_array($message['params'])
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
        $response = $this->sendMessage('querySelector');

        return $this->processElementResponse($response);
    }

    /**
     * Send a message to the server via the channel
     *
     * @param  array<string, mixed>  $params
     */
    private function sendMessage(string $method, array $params = []): Generator
    {
        $defaultParams = ['selector' => $this->selector, 'strict' => true];
        $finalParams = array_merge($defaultParams, $params);

        return Client::instance()->execute($this->frameGuid, $method, $finalParams);
    }

    /**
     * Process response and extract result value
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
     * Process response and extract string result
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
     * Process response and extract nullable string result
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
     * Process response and extract boolean result
     */
    private function processBooleanResponse(Generator $response): bool
    {
        $result = $this->processResultResponse($response);

        if (! is_bool($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Process response consuming all messages
     */
    private function processVoidResponse(Generator $response): void
    {
        foreach ($response as $message) {
            // Consume all messages to clear the response
        }
    }

    /**
     * Process response for Element creation
     */
    private function processElementResponse(Generator $response): ?Element
    {
        /** @var array{method: string|null, params: array{type: string|null, guid: string}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method'], $message['params']['type'], $message['params']['guid'])
                && $message['method'] === '__create__'
                && $message['params']['type'] === 'ElementHandle'
            ) {
                return new Element($message['params']['guid']);
            }
        }

        return null;
    }
}
