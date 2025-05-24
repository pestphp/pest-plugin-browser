<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

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

        $response = Client::instance()->execute(
            $this->guid,
            'goto',
            ['url' => $url, 'waitUntil' => 'load']
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
     * Returns the meta title.
     */
    public function title(): string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'title'
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
     * Get the value of an attribute of the first element matching the selector within the frame.
     */
    public function getAttribute(string $selector, string $attribute): ?string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'getAttribute',
            ['selector' => $selector, 'name' => $attribute],
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
     * Finds an element matching the specified selector.
     */
    public function querySelector(string $selector): ?Element
    {
        $response = Client::instance()->execute(
            $this->guid,
            'querySelector',
            ['selector' => $selector, 'strict' => true]
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

    /**
     * Finds an element by the specified role.
     *
     * @param  array<string, string|bool>  $params
     */
    public function getByRole(string $role, array $params = []): ?Element
    {
        return $this->querySelector(Selector::getByRoleSelector($role, $params));
    }

    /**
     * Finds an element by test ID.
     */
    public function getByTestId(string $testId): ?Element
    {
        $testIdAttributeName = 'data-testid';

        return $this->querySelector(Selector::getByTestIdSelector($testIdAttributeName, $testId));
    }

    /**
     * Finds an element by alt text.
     */
    public function getByAltText(string $text, bool $exact = false): ?Element
    {
        return $this->querySelector(Selector::getByAltTextSelector($text, $exact));
    }

    /**
     * Finds an element by label text.
     */
    public function getByLabel(string $text, bool $exact = false): ?Element
    {
        return $this->querySelector(Selector::getByLabelSelector($text, $exact));
    }

    /**
     * Finds an element by placeholder text.
     */
    public function getByPlaceholder(string $text, bool $exact = false): ?Element
    {
        return $this->querySelector(Selector::getByPlaceholderSelector($text, $exact));
    }

    /**
     * Finds an element by its text content.
     */
    public function getByText(string $text, bool $exact = false): ?Element
    {
        return $this->querySelector(Selector::getByTextSelector($text, $exact));
    }

    /**
     * Finds an element by its title attribute.
     */
    public function getByTitle(string $text, bool $exact = false): ?Element
    {
        return $this->querySelector(Selector::getByTitleSelector($text, $exact));
    }

    /**
     * Clicks the element matching the specified selector.
     */
    public function click(string $selector): self
    {
        $response = Client::instance()->execute(
            $this->guid,
            'click',
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
     * Double-clicks the element matching the specified selector.
     */
    public function doubleClick(string $selector): self
    {
        $response = Client::instance()->execute(
            $this->guid,
            'dblclick',
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
                return $message['result']['value'];
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
                return $message['result']['value'];
            }
        }

        return false;
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
                return $message['result']['value'];
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
}
