<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Support\Screenshot;

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
        $this->frame->goto($url);

        return $this;
    }

    /**
     * Navigates to the next page in the history.
     */
    public function forward(): self
    {
        $response = Client::instance()->execute(
            $this->guid,
            'goForward',
        );

        /** @var array{method: string|null, params: array{url: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->frame->url = $message['params']['url'] ?? '';
            }
        }

        return $this;
    }

    /**
     * Navigates to the previous page in the history.
     */
    public function back(): self
    {
        $response = Client::instance()->execute(
            $this->guid,
            'goBack',
        );

        /** @var array{method: string|null, params: array{url: string|null}} $message */
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
        $response = Client::instance()->execute(
            $this->guid,
            'reload',
            ['waitUntil' => 'load']
        );

        /** @var array{method: string|null, params: array{url: string|null}} $message */
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
        $this->frame->click($selector);

        return $this;
    }

    /**
     * Double-clicks the element matching the specified selector.
     */
    public function doubleClick(string $selector): self
    {
        $this->frame->doubleClick($selector);

        return $this;
    }

    /**
     * Finds an element matching the specified selector.
     */
    public function querySelector(string $selector): ?Element
    {
        return $this->frame->querySelector($selector);
    }

    /**
     * Returns the page's title.
     */
    public function title(): string
    {
        return $this->frame->title();
    }

    /**
     * Make screenshot of the page.
     */
    public function screenshot(?string $filename = null): void
    {
        $response = Client::instance()->execute(
            $this->guid,
            'screenshot',
            ['type' => 'png', 'fullPage' => true, 'hideCaret' => true]
        );

        /** @var array{result: array{binary: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['binary'])) {
                Screenshot::save($message['result']['binary'], $filename);
            }
        }
    }

    /**
     * Returns the value of the specified attribute of the element matching the specified selector.
     */
    public function getAttribute(string $selector, string $name): ?string
    {
        return $this->frame->getAttribute($selector, $name);
    }
}
