<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use BadMethodCallException;
use Pest\Browser\Execution;
use Pest\Browser\Page as BrowserPage;
use Pest\Browser\Playwright\Locator;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Support\GuessLocator;

final class Webpage
{
    use Concerns\HasWaitCapabilities,
        Concerns\InteractsWithElements,
        Concerns\InteractsWithFrames,
        Concerns\InteractsWithScreen,
        Concerns\InteractsWithTab,
        Concerns\InteractsWithToolbar,
        Concerns\InteractsWithViewPort,
        Concerns\MakesConsoleAssertions,
        Concerns\MakesElementAssertions,
        Concerns\MakesScreenshotAssertions,
        Concerns\MakesUrlAssertions;

    /** The current scope selector for within contexts. */
    private ?string $currentScope = null;

    /**
     * The page instance.
     */
    public function __construct(
        private readonly Page $page,
        private readonly string|BrowserPage $initialUrl,
    ) {
        //
    }

    /**
     * Dynamically call a method on the browser.
     *
     * @param  array<int, mixed>  $arguments
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $arguments): self
    {
        if ($this->initialUrl instanceof BrowserPage && method_exists($this->initialUrl, $method)) {
            array_unshift($arguments, $this);

            // @phpstan-ignore-next-line method.dynamicName
            $this->initialUrl->{$method}(...$arguments);

            return $this;
        }

        throw new BadMethodCallException("Call to undefined method [{$method}].");
    }

    /**
     * Dumps the current page's content and stops the execution.
     */
    public function dd(): never
    {
        dd($this->page->content());
    }

    /**
     * Waits for the page to load and returns the current instance.
     *
     * This automatically only runs this test + opens the browser in headed mode.
     */
    public function debug(): self
    {
        $this->wait();

        return $this;
    }

    /**
     * Gets the page's content.
     */
    public function content(): string
    {
        return $this->page->content();
    }

    /**
     * Gets the page's URL.
     */
    public function url(): string
    {
        return $this->page->url();
    }

    /**
     * Gets the page's initial URL.
     */
    public function initialUrl(): string
    {
        if ($this->initialUrl instanceof BrowserPage) {
            return $this->initialUrl->url();
        }

        return $this->initialUrl;
    }

    /**
     * Submits the first form found on the page.
     */
    public function submit(): self
    {
        $this->guessLocator('[type="submit"]')->click();

        return $this;
    }

    /**
     * Executes a script in the context of the page.
     */
    public function script(string $content): mixed
    {
        return $this->page->evaluate($content);
    }

    /**
     * Gets the page instance.
     */
    public function value(string $selector): string
    {
        return $this->guessLocator($selector)->inputValue();
    }

    public function within(string $selector, callable $callback): self
    {
        $selector = $this->resolveShorthandSelector($selector);

        $previousScope = $this->currentScope;

        $this->currentScope = $previousScope !== null ? $previousScope.' >> '.$selector : $selector;

        try {
            call_user_func($callback, $this);
        } finally {
            $this->currentScope = $previousScope;
        }

        return $this;
    }

    /**
     * Gets the locator for the given selector.
     */
    private function guessLocator(string $selector, ?string $value = null): Locator
    {
        $selector = $this->resolveShorthandSelector($selector);

        return (new GuessLocator($this->page, $this->currentScope))->for($selector, $value);
    }

    /**
     * Resolve the shorthand selector for the given page.
     */
    private function resolveShorthandSelector(string $selector): string
    {
        $shorthandElements = $this->page->shorthandElements();

        return str_replace(
            array_keys($shorthandElements), array_values($shorthandElements), $selector
        );
    }
}
