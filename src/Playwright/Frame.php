<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

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

        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return (string) $message['result']['value'];
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
     * Clicks the element matching the specified selector.
     */
    public function click(string $selector): self
    {
        $response = Client::instance()->execute(
            $this->guid,
            'click',
            ['selector' => $selector]
        );

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

        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }
}
