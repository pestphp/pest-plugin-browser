<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
use Pest\Browser\Playwright\Concerns\InteractsWithPlaywright;
use Pest\Browser\Support\Screenshot;

/**
 * @internal
 */
final class Page
{
    use InteractsWithPlaywright;

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
        $response = $this->sendMessage('goForward');
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Navigates to the previous page in the history.
     */
    public function back(): self
    {
        $response = $this->sendMessage('goBack');
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Reloads the current page.
     */
    public function reload(): self
    {
        $response = $this->sendMessage('reload', ['waitUntil' => 'load']);
        $this->processNavigationResponse($response);

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
     * Create a locator for the specified selector.
     */
    public function locator(string $selector): Locator
    {
        return $this->frame->locator($selector);
    }

    /**
     * Create a locator that matches elements by role.
     *
     * @param  array<string, string|bool>  $params
     */
    public function getByRole(string $role, array $params = []): Locator
    {
        return $this->frame->getByRole($role, $params);
    }

    /**
     * Create a locator that matches elements by test ID.
     */
    public function getByTestId(string $testId): Locator
    {
        return $this->frame->getByTestId($testId);
    }

    /**
     * Create a locator that matches elements by alt text.
     */
    public function getByAltText(string $text, bool $exact = false): Locator
    {
        return $this->frame->getByAltText($text, $exact);
    }

    /**
     * Create a locator that matches elements by label text.
     */
    public function getByLabel(string $text, bool $exact = false): Locator
    {
        return $this->frame->getByLabel($text, $exact);
    }

    /**
     * Create a locator that matches elements by placeholder text.
     */
    public function getByPlaceholder(string $text, bool $exact = false): Locator
    {
        return $this->frame->getByPlaceholder($text, $exact);
    }

    /**
     * Create a locator that matches elements by text content.
     */
    public function getByText(string $text, bool $exact = false): Locator
    {
        return $this->frame->getByText($text, $exact);
    }

    /**
     * Create a locator that matches elements by title attribute.
     */
    public function getByTitle(string $text, bool $exact = false): Locator
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
     * Returns whether the element matching the specified selector is editable.
     */
    public function isEditable(string $selector): bool
    {
        return $this->frame->isEditable($selector);
    }

    /**
     * Returns whether the element matching the specified selector is disabled.
     */
    public function isDisabled(string $selector): bool
    {
        return $this->frame->isDisabled($selector);
    }

    /**
     * Finds all elements matching the specified selector.
     *
     * @return Element[]
     */
    public function querySelectorAll(string $selector): array
    {
        return $this->frame->querySelectorAll($selector);
    }

    /**
     * Sets the content of the page.
     */
    public function setContent(string $html): self
    {
        $this->frame->setContent($html);

        return $this;
    }

    /**
     * Fill an input field with text.
     */
    public function fill(string $selector, string $value): self
    {
        $this->frame->fill($selector, $value);

        return $this;
    }

    /**
     * Check a checkbox or radio button.
     */
    public function check(string $selector): self
    {
        $this->frame->check($selector);

        return $this;
    }

    /**
     * Uncheck a checkbox.
     */
    public function uncheck(string $selector): self
    {
        $this->frame->uncheck($selector);

        return $this;
    }

    /**
     * Hover over an element.
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
        $this->frame->hover(
            $selector,
            $force,
            $modifiers,
            $noWaitAfter,
            $position,
            $strict,
            $timeout,
            $trial
        );

        return $this;
    }

    /**
     * Focus an element.
     */
    public function focus(string $selector): self
    {
        $this->frame->focus($selector);

        return $this;
    }

    /**
     * Press a key on an element.
     */
    public function press(string $selector, string $key): self
    {
        $this->frame->press($selector, $key);

        return $this;
    }

    /**
     * Type text into an element.
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
     * Waits for the selector to satisfy state option.
     *
     * @param  array<string, mixed>|null  $options  Additional options like state, strict, timeout
     */
    public function waitForSelector(string $selector, ?array $options = null): ?Element
    {
        return $this->frame->waitForSelector($selector, $options);
    }

    // Common methods are now provided by the InteractsWithPlaywright trait

    /**
     * Override processNavigationResponse for Page specific behavior
     */
    private function processNavigationResponse(Generator $response): void
    {
        /** @var array{method: string|null, params: array{url: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->frame->url = $message['params']['url'] ?? '';
            }
        }
    }
}
