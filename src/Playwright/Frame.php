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
}
