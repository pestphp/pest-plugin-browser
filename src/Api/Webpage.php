<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Playwright\Locator;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Support\GuessLocator;

final readonly class Webpage
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

    /**
     * The page instance.
     */
    public function __construct(
        private Page $page,
        private string $initialUrl,
    ) {
        //
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
     * Submits the first form found on the page.
     */
    public function submit(): self
    {
        $this->guessClickableLocator('[type="submit"]')->click();

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

    /**
     * Gets the locator for the given selector.
     */
    private function guessLocator(string $selector, ?string $value = null): Locator
    {
        return (new GuessLocator($this->page))->for($selector, $value);
    }

    private function guessClickableLocator(string $selector): Locator
    {
        return (new GuessLocator($this->page))->forClickable($selector);
    }

    private function guessFieldLocator(string $selector): Locator
    {
        return (new GuessLocator($this->page))->forField($selector);
    }

    private function guessCheckableLocator(string $selector, ?string $value = null): Locator
    {
        return (new GuessLocator($this->page))->forCheckable($selector, $value);
    }

    private function guessSelectableLocator(string $selector): Locator
    {
        return (new GuessLocator($this->page))->forSelectable($selector);
    }
}
