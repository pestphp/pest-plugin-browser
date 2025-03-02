<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class Page
{
    /**
     * Constructs new page
     */
    public function __construct(
        public string $guid,
        public Frame $frame,
    ) {
        //
    }

    /**
     * Get the current URL of the page.
     */
    public function url(): string
    {
        return $this->frame->url;
    }

    /**
     * Navigates to the given URL.
     */
    public function goto(string $url): self
    {
        if ($this->frame->url === $url) {
            return $this;
        }

        $response = Client::getInstance()->execute(
            $this->frame->guid,
            'goto',
            ['url' => $url, 'waitUntil' => 'domcontentloaded']
        );

        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->frame->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }

    /**
     * Reloads the current page.
     */
    public function reload(): self
    {
        $response = Client::getInstance()->execute(
            $this->guid,
            'reload',
            ['waitUntil' => 'domcontentloaded']
        );

        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->frame->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }

    /**
     * Clicks the element matching the specified selector.
     */
    public function click(string $selector): self
    {
        $response = Client::getInstance()->execute(
            $this->frame->guid,
            'click',
            ['selector' => $selector]
        );

        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->frame->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }

    /**
     * Double-clicks the element matching the specified selector.
     */
    public function doubleClick(string $selector): self
    {
        $response = Client::getInstance()->execute(
            $this->frame->guid,
            'dblclick',
            ['selector' => $selector]
        );

        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->frame->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }

    /**
     * Finds an element matching the specified selector.
     */
    public function querySelector(string $selector): ?Element
    {
        $response = Client::getInstance()->execute(
            $this->frame->guid,
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
     * Returns the page's title.
     */
    public function title(): string
    {
        $response = Client::getInstance()->execute($this->frame->guid, 'title');

        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return '';
    }
}
