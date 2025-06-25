<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
use Pest\Browser\Playwright\Concerns\InteractsWithPlaywright;
use Pest\Browser\Support\Selector;
use RuntimeException;

/**
 * @internal
 */
final readonly class Locator
{
    use InteractsWithPlaywright;

    /**
     * Creates a new locator instance.
     */
    public function __construct(
        private string $frameGuid,
        private string $selector,
        private bool $strictMode = true,
    ) {
        //
    }

    /**
     * Get the selector string for this locator.
     */
    public function selector(): string
    {
        return $this->selector;
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
        $response = $this->sendMessage('isDisabled');

        return $this->processBooleanResponse($response);
    }

    /**
     * Check if element matching the locator is hidden.
     */
    public function isHidden(): bool
    {
        $response = $this->sendMessage('isHidden');

        return $this->processBooleanResponse($response);
    }

    /**
     * Check if element matching the locator is editable.
     */
    public function isEditable(): bool
    {
        try {
            $response = $this->sendMessage('isEditable');

            return $this->processBooleanResponse($response);
        } catch (RuntimeException $e) {
            // If the element is not a form element or contenteditable, return false
            if (str_contains($e->getMessage(), 'not an <input>, <textarea>, <select> or [contenteditable]')) {
                return false;
            }

            // Re-throw other exceptions
            throw $e;
        }
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
     * @param  array<int, string>|string|null  $values
     * @param  array<string, mixed>|null  $options  Can include 'label', 'index', 'force', 'timeout', etc.
     * @return array<array-key, string>
     */
    public function selectOption(array|string|null $values = null, ?array $options = null): array
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }

        $params = $options ?? [];

        // Handle different selection criteria - values takes precedence if provided
        if ($values !== null) {
            $params['value'] = is_array($values) ? $values : [$values];
        }
        // Other criteria (label, index) should be provided via $options

        $response = $this->sendMessage('selectOption', $params);
        $result = $this->processArrayResponse($response);

        // Ensure all values are strings for type safety
        return array_map(function (mixed $value): string {
            if (is_string($value)) {
                return $value;
            }
            if (is_scalar($value) || $value === null) {
                return (string) $value;
            }

            return '';
        }, $result);
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
     *
     * @param  array<string, mixed>|null  $options
     */
    public function inputValue(?array $options = null): string
    {
        $response = $this->sendMessage('inputValue', $options ?? []);

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
        return $this->locator('nth=0');
    }

    /**
     * Create a locator for the last element matching the selector.
     */
    public function last(): self
    {
        return $this->locator('nth=-1');
    }

    /**
     * Create a locator for the nth element matching the selector.
     */
    public function nth(int $index): self
    {
        return $this->locator("nth={$index}");
    }

    /**
     * Create a locator that matches elements containing the specified text.
     */
    public function getByText(string $text, bool $exact = false): self
    {
        $textSelector = Selector::getByTextSelector($text, $exact);

        return $this->locator($textSelector);
    }

    /**
     * Create a locator that matches elements with the specified role.
     *
     * @param  array<string, string|bool>  $params
     */
    public function getByRole(string $role, array $params = []): self
    {
        $roleSelector = Selector::getByRoleSelector($role, $params);

        return $this->locator($roleSelector);
    }

    /**
     * Create a locator that matches elements with the specified test ID.
     */
    public function getByTestId(string $testId): self
    {
        $testIdSelector = Selector::getByTestIdSelector('data-testid', $testId);

        return $this->locator($testIdSelector);
    }

    /**
     * Create a locator that matches elements with the specified alt text.
     */
    public function getByAltText(string $text, bool $exact = false): self
    {
        $altTextSelector = Selector::getByAltTextSelector($text, $exact);

        return $this->locator($altTextSelector);
    }

    /**
     * Create a locator that matches elements with the specified label.
     */
    public function getByLabel(string $text, bool $exact = false): self
    {
        $labelSelector = Selector::getByLabelSelector($text, $exact);

        return $this->locator($labelSelector);
    }

    /**
     * Create a locator that matches elements with the specified placeholder.
     */
    public function getByPlaceholder(string $text, bool $exact = false): self
    {
        $placeholderSelector = Selector::getByPlaceholderSelector($text, $exact);

        return $this->locator($placeholderSelector);
    }

    /**
     * Create a locator that matches elements with the specified title.
     */
    public function getByTitle(string $text, bool $exact = false): self
    {
        $titleSelector = Selector::getByTitleSelector($text, $exact);

        return $this->locator($titleSelector);
    }

    /**
     * Create a locator using a CSS selector or other selector relative to this locator.
     */
    public function locator(string $selector): self
    {
        return new self($this->frameGuid, $this->selector.' >> '.$selector);
    }

    /**
     * Filter this locator to only match elements that also match the given criteria.
     *
     * @param  string|array<string, mixed>  $options  Selector string or filter options array
     */
    public function filter(string|array $options = []): self
    {
        // Handle backward compatibility - if string is passed, treat as selector
        if (is_string($options)) {
            return $this->locator($options);
        }

        // Handle array options for enhanced filtering
        $filters = [];

        if (isset($options['hasText'])) {
            $text = $options['hasText'];
            if (is_string($text)) {
                $filters[] = ":has-text(\"$text\")";
            }
        }

        if (isset($options['hasNotText'])) {
            $text = $options['hasNotText'];
            if (is_string($text)) {
                $filters[] = ":not(:has-text(\"$text\"))";
            }
        }

        if (isset($options['has'])) {
            $locator = $options['has'];
            if ($locator instanceof self) {
                $filters[] = ":has({$locator->selector})";
            }
        }

        if (isset($options['hasNot'])) {
            $locator = $options['hasNot'];
            if ($locator instanceof self) {
                $filters[] = ":not(:has({$locator->selector}))";
            }
        }

        $filterString = implode('', $filters);

        return new self($this->frameGuid, $this->selector.$filterString);
    }

    /**
     * Filter this locator to only match elements that also match the given selector.
     * Legacy method for backward compatibility.
     */
    public function filterBySelector(string $selector): self
    {
        return new self($this->frameGuid, $this->selector.':has('.$selector.')');
    }

    /**
     * Count the number of elements matching this locator.
     */
    public function count(): int
    {
        $count = 0;

        for ($i = 0; $i < 100; $i++) {
            $locator = $this->nth($i);

            $found = $locator->elementHandle();

            if (! $found instanceof Element) {
                break;
            }

            $count++;
        }

        return $count;
    }

    /**
     * Get the Element handle for this locator. Returns the first element matching the locator.
     *
     * @internal
     */
    public function elementHandle(): ?Element
    {
        $response = $this->sendMessage('querySelector');

        return $this->processElementCreationResponse($response);
    }

    /**
     * Returns an array of all locators matching this locator.
     *
     * @return array<int, self>
     */
    public function all(): array
    {
        $locators = [];
        $count = $this->count();

        for ($i = 0; $i < $count; $i++) {
            $locators[] = $this->nth($i);
        }

        return $locators;
    }

    /**
     * Returns an array of all inner texts for elements matching this locator.
     *
     * @return array<int, string>
     */
    public function allInnerTexts(): array
    {
        $texts = [];
        $count = $this->count();

        for ($i = 0; $i < $count; $i++) {
            $element = $this->nth($i)->elementHandle();
            if ($element instanceof Element) {
                $texts[] = $element->innerText();
            }
        }

        return $texts;
    }

    /**
     * Returns an array of all text contents for elements matching this locator.
     *
     * @return array<int, string>
     */
    public function allTextContents(): array
    {
        // Alternative implementation using existing methods
        $texts = [];
        $count = $this->count();

        for ($i = 0; $i < $count; $i++) {
            $element = $this->nth($i)->elementHandle();
            if ($element instanceof Element) {
                $textContent = $element->textContent();
                $texts[] = $textContent ?? '';
            }
        }

        return $texts;
    }

    /**
     * Creates a locator matching both this locator and the argument locator.
     */
    public function and(self $locator): self
    {
        return new self($this->frameGuid, $this->selector.':is('.$locator->selector.')');
    }

    /**
     * Creates a locator matching either this locator or the argument locator.
     */
    public function or(self $locator): self
    {
        return new self($this->frameGuid, $this->selector.', '.$locator->selector);
    }

    /**
     * Returns the bounding box of the element, or null if not visible.
     *
     * @return array{x: float, y: float, width: float, height: float}|null
     */
    public function boundingBox(): ?array
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            return null;
        }

        return $element->boundingBox();
    }

    /**
     * Select all text in the element.
     */
    public function selectText(): void
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }
        $element->selectText();
    }

    /**
     * Take a screenshot of the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function screenshot(?array $options = null): string
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }

        return $element->screenshot($options);
    }

    /**
     * Scroll element into view if needed.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function scrollIntoViewIfNeeded(?array $options = null): void
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }
        $element->scrollIntoViewIfNeeded($options);
    }

    /**
     * Highlight the element (useful for debugging).
     */
    public function highlight(): void
    {
        $response = $this->sendMessage('highlight');
        $this->processVoidResponse($response);
    }

    /**
     * Tap the element (touch screen interaction).
     *
     * @param  array<string, mixed>|null  $options
     */
    public function tap(?array $options = null): void
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }
        $element->tap($options);
    }

    /**
     * Set input files for a file input element.
     */
    public function setInputFiles(string $path): void
    {
        $params = ['localPaths' => [$path]];
        $response = $this->sendMessage('setInputFiles', $params);

        $this->processVoidResponse($response);
    }

    /**
     * Wait for element to reach a specific state.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitForElementState(string $state, ?array $options = null): void
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }
        $element->waitForElementState($state, $options);
    }

    /**
     * Get the content frame for iframe elements.
     *
     * @return object|null Frame object with guid property, or null if not an iframe
     */
    public function contentFrame(): ?object
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }

        return $element->contentFrame();
    }

    /**
     * Get the owner frame of the element.
     *
     * @return object|null Frame object with guid property, or null if no owner frame
     */
    public function ownerFrame(): ?object
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }

        return $element->ownerFrame();
    }

    /**
     * Create a frame locator for iframe elements.
     */
    public function frameLocator(string $selector): self
    {
        // This would typically return a FrameLocator, but for simplicity
        // we'll return a regular locator pointing to the frame content
        return $this->locator($selector);
    }

    /**
     * Get the page this locator belongs to.
     * For now, this returns the frame GUID as a simple identifier.
     */
    public function page(): string
    {
        return $this->frameGuid;
    }

    /**
     * Drag this element to the target locator.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function dragTo(self $target, ?array $options = null): void
    {
        $params = array_merge([
            'source' => $this->selector,
            'target' => $target->selector,
        ], $options ?? []);
        $response = $this->sendMessage('dragAndDrop', $params);

        $this->processVoidResponse($response);
    }

    /**
     * Wait for the locator to match a specific state.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitForState(string $state = 'visible', ?array $options = null): void
    {
        $element = $this->elementHandle();
        if (! $element instanceof Element) {
            throw new RuntimeException('Element not found');
        }
        $element->waitForElementState($state, $options);
    }

    /**
     * Send a message to the server via the channel with locator-specific parameters
     *
     * @param  array<string, mixed>  $params
     */
    private function sendMessage(string $method, array $params = []): Generator
    {
        $defaultParams = ['selector' => $this->selector];
        if (! isset($params['strict'])) {
            $defaultParams['strict'] = $this->strictMode;
        }
        $finalParams = array_merge($defaultParams, $params);

        return Client::instance()->execute($this->frameGuid, $method, $finalParams);
    }
}
