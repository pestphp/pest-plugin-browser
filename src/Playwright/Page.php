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
     * Finds an element by the specified role.
     *
     * @param  array<string, string|bool>  $params
     */
    public function getByRole(string $role, array $params = []): ?Element
    {
        return $this->frame->getByRole($role, $params);
    }

    /**
     * Finds an element by test ID.
     */
    public function getByTestId(string $testId): ?Element
    {
        return $this->frame->getByTestId($testId);
    }

    /**
     * Finds an element by alt text.
     */
    public function getByAltText(string $text, bool $exact = false): ?Element
    {
        return $this->frame->getByAltText($text, $exact);
    }

    /**
     * Finds an element by label text.
     */
    public function getByLabel(string $text, bool $exact = false): ?Element
    {
        return $this->frame->getByLabel($text, $exact);
    }

    /**
     * Finds an element by placeholder text.
     */
    public function getByPlaceholder(string $text, bool $exact = false): ?Element
    {
        return $this->frame->getByPlaceholder($text, $exact);
    }

    /**
     * Finds an element by its text content.
     */
    public function getByText(string $text, bool $exact = false): ?Element
    {
        return $this->frame->getByText($text, $exact);
    }

    /**
     * Finds an element by its title attribute.
     */
    public function getByTitle(string $text, bool $exact = false): ?Element
    {
        return $this->frame->getByTitle($text, $exact);
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

    /**
     * Gets the full HTML contents of the page, including the doctype.
     */
    public function content(): string
    {
        return $this->frame->content();
    }

    /**
     * Returns element's inner text.
     */
    public function innerText(string $selector): string
    {
        return $this->frame->innerText($selector);
    }

    /**
     * Returns element's text content.
     */
    public function textContent(string $selector): ?string
    {
        return $this->frame->textContent($selector);
    }

    /**
     * Gets the input value of the element matching the specified selector.
     */
    public function inputValue(string $selector): string
    {
        return $this->frame->inputValue($selector);
    }

    /**
     * Returns whether the element matching the specified selector is enabled.
     */
    public function isEnabled(string $selector): bool
    {
        return $this->frame->isEnabled($selector);
    }

    /**
     * Returns whether the element matching the specified selector is visible.
     */
    public function isVisible(string $selector): bool
    {
        return $this->frame->isVisible($selector);
    }

    /**
     * Returns whether the element matching the specified selector is hidden.
     */
    public function isHidden(string $selector): bool
    {
        return $this->frame->isHidden($selector);
    }

    /**
     * Returns whether the element matching the specified selector is checked.
     */
    public function isChecked(string $selector): bool
    {
        return $this->frame->isChecked($selector);
    }

    /**
     * Fills the element matching the specified selector with the given value.
     */
    public function fill(string $selector, string $value): self
    {
        $this->frame->fill($selector, $value);
        return $this;
    }

    /**
     * Checks the element matching the specified selector.
     */
    public function check(string $selector): self
    {
        $this->frame->check($selector);
        return $this;
    }

    /**
     * Unchecks the element matching the specified selector.
     */
    public function uncheck(string $selector): self
    {
        $this->frame->uncheck($selector);
        return $this;
    }



    /**
     * Hovers over the element matching the specified selector.
     */
    public function hover(string $selector): self
    {
        $this->frame->hover($selector);
        return $this;
    }

    /**
     * Focuses the element matching the specified selector.
     */
    public function focus(string $selector): self
    {
        $this->frame->focus($selector);
        return $this;
    }

    /**
     * Presses a key on the element matching the specified selector.
     */
    public function press(string $selector, string $key): self
    {
        $this->frame->press($selector, $key);
        return $this;
    }

    /**
     * Types text into the element matching the specified selector.
     */
    public function type(string $selector, string $text): self
    {
        $this->frame->type($selector, $text);
        return $this;
    }

    /**
     * Performs drag and drop operation.
     */
    public function dragAndDrop(string $source, string $target): self
    {
        $this->frame->dragAndDrop($source, $target);
        return $this;
    }

    /**
     * Waits for the specified load state.
     */
    public function waitForLoadState(string $state = 'load'): self
    {
        $this->frame->waitForLoadState($state);
        return $this;
    }

    /**
     * Waits for the frame to navigate to the given URL.
     */
    public function waitForURL(string $url): self
    {
        $this->frame->waitForURL($url);
        return $this;
    }

    /**
     * Executes JavaScript in the frame context.
     */
    public function evaluate(string $expression, ?array $args = null): mixed
    {
        return $this->frame->evaluate($expression, $args);
    }
}
