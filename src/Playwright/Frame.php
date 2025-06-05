<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Playwright\Concerns\InteractsWithPlaywright;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\Selector;
use RuntimeException;

/**
 * @internal
 */
final class Frame
{
    use InteractsWithPlaywright;

    /**
     * Constructs new frame.
     */
    public function __construct(
        public string $guid,
        public string $url,
    ) {
        //
    }

    /**
     * Navigates to the given URL.
     */
    public function goto(string $url): self
    {
        $url = mb_ltrim($url, '/');
        $url = ServerManager::instance()->http()->url() . '/' . $url;

        if ($this->url === $url) {
            return $this;
        }

        $response = $this->sendMessage('goto', ['url' => $url, 'waitUntil' => 'load']);
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Returns the meta title.
     */
    public function title(): string
    {
        $response = $this->sendMessage('title');

        return $this->processStringResponse($response);
    }

    /**
     * Get the value of an attribute of the first element matching the selector within the frame.
     */
    public function getAttribute(string $selector, string $attribute): ?string
    {
        $response = $this->sendMessage('getAttribute', ['selector' => $selector, 'name' => $attribute]);

        return $this->processNullableStringResponse($response);
    }

    /**
     * Finds an element matching the specified selector.
     *
     * @deprecated Use locator($selector)->elementHandle() instead for Element compatibility, or use locator($selector) for Locator-first approach
     */
    public function querySelector(string $selector): ?Element
    {
        return $this->locator($selector)->elementHandle();
    }

    /**
     * Finds all elements matching the specified selector.
     *
     * @return Element[]
     */
    public function querySelectorAll(string $selector): array
    {
        $response = $this->sendMessage('querySelectorAll', ['selector' => $selector]);
        $elements = [];

        foreach ($response as $message) {
            if (
                isset($message['method']) && $message['method'] === '__create__'
                && isset($message['params']['type']) && $message['params']['type'] === 'ElementHandle'
                && isset($message['params']['guid'])
            ) {
                $elements[] = new Element($message['params']['guid']);
            }
        }

        return $elements;
    }

    /**
     * Create a locator for the specified selector.
     */
    public function locator(string $selector): Locator
    {
        return new Locator($this->guid, $selector);
    }

    /**
     * Create a locator that matches elements by role.
     *
     * @param  array<string, string|bool>  $params
     */
    public function getByRole(string $role, array $params = []): Locator
    {
        return new Locator($this->guid, Selector::getByRoleSelector($role, $params));
    }

    /**
     * Create a locator that matches elements by test ID.
     */
    public function getByTestId(string $testId): Locator
    {
        $testIdAttributeName = 'data-testid';

        return new Locator($this->guid, Selector::getByTestIdSelector($testIdAttributeName, $testId));
    }

    /**
     * Create a locator that matches elements by alt text.
     */
    public function getByAltText(string $text, bool $exact = false): Locator
    {
        return new Locator($this->guid, Selector::getByAltTextSelector($text, $exact));
    }

    /**
     * Create a locator that matches elements by label text.
     */
    public function getByLabel(string $text, bool $exact = false): Locator
    {
        return new Locator($this->guid, Selector::getByLabelSelector($text, $exact));
    }

    /**
     * Create a locator that matches elements by placeholder text.
     */
    public function getByPlaceholder(string $text, bool $exact = false): Locator
    {
        return new Locator($this->guid, Selector::getByPlaceholderSelector($text, $exact));
    }

    /**
     * Create a locator that matches elements by text content.
     */
    public function getByText(string $text, bool $exact = false): Locator
    {
        return new Locator($this->guid, Selector::getByTextSelector($text, $exact));
    }

    /**
     * Create a locator that matches elements by title attribute.
     */
    public function getByTitle(string $text, bool $exact = false): Locator
    {
        return new Locator($this->guid, Selector::getByTitleSelector($text, $exact));
    }

    /**
     * Clicks the element matching the specified selector.
     */
    public function click(string $selector): self
    {
        $response = $this->sendMessage('click', ['selector' => $selector]);
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Double-clicks the element matching the specified selector.
     */
    public function doubleClick(string $selector): self
    {
        $response = $this->sendMessage('dblclick', ['selector' => $selector]);
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Gets the full HTML contents of the frame, including the doctype.
     */
    public function content(): string
    {
        $response = $this->sendMessage('content');

        return $this->processStringResponse($response);
    }

    /**
     * Returns whether the element is enabled.
     */
    public function isEnabled(string $selector): bool
    {
        $response = $this->sendMessage('isEnabled', ['selector' => $selector]);

        return $this->processBooleanResponse($response);
    }

    /**
     * Returns whether the element is visible.
     */
    public function isVisible(string $selector): bool
    {
        $response = $this->sendMessage('isVisible', ['selector' => $selector]);

        return $this->processBooleanResponse($response);
    }

    /**
     * Returns whether the element is hidden.
     */
    public function isHidden(string $selector): bool
    {
        return ! $this->isVisible($selector);
    }

    /**
     * Returns whether the element is editable.
     */
    public function isEditable(string $selector): bool
    {
        try {
            $response = $this->sendMessage('isEditable', ['selector' => $selector]);

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
     * Returns whether the element is disabled.
     */
    public function isDisabled(string $selector): bool
    {
        return ! $this->isEnabled($selector);
    }

    /**
     * Fills a form field with the given value.
     */
    public function fill(string $selector, string $value): self
    {
        $response = $this->sendMessage('fill', ['selector' => $selector, 'value' => $value]);
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Returns element's inner text.
     */
    public function innerText(string $selector): string
    {
        $response = $this->sendMessage('innerText', ['selector' => $selector]);

        return $this->processStringResponse($response);
    }

    /**
     * Returns element's text content.
     */
    public function textContent(string $selector): ?string
    {
        $response = $this->sendMessage('textContent', ['selector' => $selector]);

        return $this->processNullableStringResponse($response);
    }

    /**
     * Returns the input value for input elements.
     */
    public function inputValue(string $selector): string
    {
        $response = $this->sendMessage('inputValue', ['selector' => $selector]);

        return $this->processStringResponse($response);
    }

    /**
     * Checks whether the element is checked (for checkboxes and radio buttons).
     */
    public function isChecked(string $selector): bool
    {
        $response = $this->sendMessage('isChecked', ['selector' => $selector]);

        return $this->processBooleanResponse($response);
    }

    /**
     * Checks the element (for checkboxes and radio buttons).
     */
    public function check(string $selector): self
    {
        $response = $this->sendMessage('check', ['selector' => $selector]);
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Unchecks the element (for checkboxes and radio buttons).
     */
    public function uncheck(string $selector): self
    {
        $response = $this->sendMessage('uncheck', ['selector' => $selector]);
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Hovers over the element matching the specified selector.
     */
    public function hover(
        string $selector,
        ?bool $force = null,
        ?array $modifiers = null,
        ?bool $noWaitAfter = null,
        ?array $position = null,
        ?bool $strict = null,
        ?int $timeout = null,
        ?bool $trial = null
    ): self {
        $params = ['selector' => $selector];

        if ($force !== null) {
            $params['force'] = $force;
        }
        if ($modifiers !== null) {
            $params['modifiers'] = $modifiers;
        }
        if ($noWaitAfter !== null) {
            $params['noWaitAfter'] = $noWaitAfter;
        }
        if ($position !== null) {
            $params['position'] = $position;
        }
        if ($strict !== null) {
            $params['strict'] = $strict;
        }
        if ($timeout !== null) {
            $params['timeout'] = $timeout;
        }
        if ($trial !== null) {
            $params['trial'] = $trial;
        }

        $response = $this->sendMessage('hover', $params);
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Focuses the element matching the specified selector.
     */
    public function focus(string $selector): self
    {
        $response = $this->sendMessage('focus', ['selector' => $selector]);
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Presses a key on the element matching the specified selector.
     */
    public function press(string $selector, string $key): self
    {
        $response = $this->sendMessage('press', ['selector' => $selector, 'key' => $key]);
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Types text into the element matching the specified selector.
     */
    public function type(string $selector, string $text): self
    {
        $response = $this->sendMessage('type', ['selector' => $selector, 'text' => $text]);
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Waits for the specified load state.
     */
    public function waitForLoadState(string $state = 'load'): self
    {
        Client::instance()->execute(
            $this->guid,
            'waitForLoadState',
            ['state' => $state]
        );

        return $this;
    }

    /**
     * Waits for navigation to the specified URL.
     */
    public function waitForURL(string $url): self
    {
        Client::instance()->execute(
            $this->guid,
            'waitForURL',
            ['url' => $url]
        );

        return $this;
    }

    /**
     * Waits for the selector to satisfy state option.
     *
     * @param  array<string, mixed>|null  $options  Additional options like state, strict, timeout
     */
    public function waitForSelector(string $selector, ?array $options = null): ?Element
    {
        $params = array_merge(['selector' => $selector], $options ?? []);
        $response = $this->sendMessage('waitForSelector', $params);

        return $this->processElementCreationResponse($response);
    }

    /**
     * Performs drag and drop operation.
     */
    public function dragAndDrop(string $source, string $target): self
    {
        $response = $this->sendMessage('dragAndDrop', ['source' => $source, 'target' => $target]);
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Sets the content of the frame.
     */
    public function setContent(string $html): self
    {
        $response = $this->sendMessage('setContent', ['html' => $html]);
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Selects option(s) in a select element.
     *
     * @param  string|array|null  $value
     * @param  string|array|null  $label
     * @param  int|array|null  $index
     */
    public function selectOption(
        string $selector,
        $value = null,
        $label = null,
        $index = null,
        ?bool $force = null,
        ?bool $noWaitAfter = null,
        ?bool $strict = null,
        ?int $timeout = null
    ): self {
        $params = ['selector' => $selector];

        // Add the appropriate selection criteria - choose only one
        if ($value !== null) {
            $params['value'] = is_array($value) ? $value : [$value];
        } elseif ($label !== null) {
            $params['label'] = is_array($label) ? $label : [$label];
        } elseif ($index !== null) {
            $params['index'] = is_array($index) ? $index : [$index];
        }

        // Add optional parameters
        if ($force !== null) {
            $params['force'] = $force;
        }
        if ($noWaitAfter !== null) {
            $params['noWaitAfter'] = $noWaitAfter;
        }
        if ($strict !== null) {
            $params['strict'] = $strict;
        }
        if ($timeout !== null) {
            $params['timeout'] = $timeout;
        }

        $response = $this->sendMessage('selectOption', $params);
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Evaluates a JavaScript expression in the frame context.
     *
     * @param  mixed  $arg
     * @return mixed
     */
    public function evaluate(string $pageFunction, $arg = null)
    {
        $params = ['expression' => $pageFunction];

        if ($arg !== null) {
            $params['arg'] = $arg;
        }

        $response = $this->sendMessage('evaluate', $params);

        return $this->processResultResponse($response);
    }

    /**
     * Evaluates a JavaScript expression and returns a JSHandle.
     */
    public function evaluateHandle(string $pageFunction, $arg = null): mixed
    {
        $params = ['expression' => $pageFunction];

        if ($arg !== null) {
            $params['arg'] = $arg;
        }

        $response = $this->sendMessage('evaluateHandle', $params);

        return $this->processResultResponse($response);
    }

    // These methods are now provided by the InteractsWithPlaywright trait
}
