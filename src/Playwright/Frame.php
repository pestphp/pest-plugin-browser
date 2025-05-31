<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
use Pest\Browser\Support\Selector;

/**
 * @internal
 */
final class Frame
{
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
     * Send a message to the server via the channel
     *
     * @param  array<string, mixed>  $params
     */
    private function sendMessage(string $method, array $params = []): Generator
    {
        return Client::instance()->execute($this->guid, $method, $params);
    }

    /**
     * Process navigation response messages
     */
    private function processNavigationResponse(Generator $response): void
    {
        /** @var array{method: string|null, params: array{url: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->url = $message['params']['url'] ?? '';
            }
        }
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
     * Process response to handle element creation messages.
     */
    private function processElementCreationResponse(Generator $response): ?Element
    {
        foreach ($response as $message) {
            if (
                isset($message['method']) && $message['method'] === '__create__'
                && isset($message['params']['type']) && $message['params']['type'] === 'ElementHandle'
                && isset($message['params']['guid'])
            ) {
                return new Element($message['params']['guid']);
            }
        }

        return null;
    }

    /**
     * Gets the full HTML contents of the frame, including the doctype.
     */
    public function content(): string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'content'
        );

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return '';
    }

    /**
     * Returns whether the element is enabled.
     */
    public function isEnabled(string $selector): bool
    {
        $response = Client::instance()->execute(
            $this->guid,
            'isEnabled',
            ['selector' => $selector]
        );

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return (bool) $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Returns whether the element is visible.
     */
    public function isVisible(string $selector): bool
    {
        $response = Client::instance()->execute(
            $this->guid,
            'isVisible',
            ['selector' => $selector]
        );

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return (bool) $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Returns whether the element is hidden.
     */
    public function isHidden(string $selector): bool
    {
        return !$this->isVisible($selector);
    }

    /**
     * Fills a form field with the given value.
     */
    public function fill(string $selector, string $value): self
    {
        $response = Client::instance()->execute(
            $this->guid,
            'fill',
            ['selector' => $selector, 'value' => $value]
        );

        /** @var array{method: string|null, params: array{url: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }

    /**
     * Returns element's inner text.
     */
    public function innerText(string $selector): string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'innerText',
            ['selector' => $selector]
        );

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return '';
    }

    /**
     * Returns element's text content.
     */
    public function textContent(string $selector): ?string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'textContent',
            ['selector' => $selector]
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
     * Returns the input value for input elements.
     */
    public function inputValue(string $selector): string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'inputValue',
            ['selector' => $selector]
        );

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return '';
    }

    /**
     * Checks whether the element is checked (for checkboxes and radio buttons).
     */
    public function isChecked(string $selector): bool
    {
        $response = Client::instance()->execute(
            $this->guid,
            'isChecked',
            ['selector' => $selector]
        );

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return (bool) $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Checks the element (for checkboxes and radio buttons).
     */
    public function check(string $selector): self
    {
        $response = Client::instance()->execute(
            $this->guid,
            'check',
            ['selector' => $selector]
        );

        /** @var array{method: string|null, params: array{url: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }

    /**
     * Unchecks the element (for checkboxes and radio buttons).
     */
    public function uncheck(string $selector): self
    {
        $response = Client::instance()->execute(
            $this->guid,
            'uncheck',
            ['selector' => $selector]
        );

        /** @var array{method: string|null, params: array{url: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }



    /**
     * Hovers over the element matching the specified selector.
     */
    public function hover(string $selector): self
    {
        Client::instance()->execute(
            $this->guid,
            'hover',
            ['selector' => $selector]
        );

        return $this;
    }

    /**
     * Focuses the element matching the specified selector.
     */
    public function focus(string $selector): self
    {
        Client::instance()->execute(
            $this->guid,
            'focus',
            ['selector' => $selector]
        );

        return $this;
    }

    /**
     * Presses a key on the element matching the specified selector.
     */
    public function press(string $selector, string $key): self
    {
        Client::instance()->execute(
            $this->guid,
            'press',
            ['selector' => $selector, 'key' => $key]
        );

        return $this;
    }

    /**
     * Types text into the element matching the specified selector.
     */
    public function type(string $selector, string $text): self
    {
        Client::instance()->execute(
            $this->guid,
            'type',
            ['selector' => $selector, 'text' => $text]
        );

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
     * Evaluates JavaScript in the frame context.
     *
     * @param  mixed  $arg
     * @return mixed
     */
    public function evaluate(string $pageFunction, $arg = null)
    {
        $params = ['pageFunction' => $pageFunction];
        if ($arg !== null) {
            $params['arg'] = $arg;
        }

        $response = Client::instance()->execute(
            $this->guid,
            'evaluate',
            $params
        );

        /** @var array{result: array{value: mixed}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return null;
    }

    /**
     * Performs drag and drop operation.
     */
    public function dragAndDrop(string $source, string $target): self
    {
        Client::instance()->execute(
            $this->guid,
            'dragAndDrop',
            ['source' => $source, 'target' => $target]
        );

        return $this;
    }

    /**
     * Sets the content of the frame.
     */
    public function setContent(string $html): self
    {
        Client::instance()->execute(
            $this->guid,
            'setContent',
            ['html' => $html]
        );

        return $this;
    }
    /**
     * Waits for an event to be emitted by the frame.
     *
     * @param  string  $eventName  The name of the event to wait for.
     */
    public function waitForEvent(string $eventName): void
    {
        $response = Client::instance()->execute(
            $this->guid,
            'waitForEvent',
            ['event' => $eventName]
        );

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }
}
