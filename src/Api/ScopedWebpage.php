<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Playwright\Locator;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Support\GuessLocator;

final readonly class ScopedWebpage
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
        Concerns\MakesScreenshotAssertions;

    public function __construct(
        private Page $page,
        private string $initialUrl,
        private string $scope,
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
     * Submits the first form found on the page.
     */
    public function submit(): self
    {
        $this->guessLocator('[type="submit"]')->click();

        return $this;
    }

    /**
     * Gets the page instance.
     */
    public function value(string $selector): string
    {
        return $this->guessLocator($selector)->inputValue();
    }

    /**
     * Limits the scope of subsequent interactions to within a specific element.
     */
    public function within(string $selector, callable $callback): self
    {
        $nestedScope = $this->scope.' '.$selector;
        $scopedWebpage = new self($this->page, $this->initialUrl, $nestedScope);

        $callback($scopedWebpage);

        return $this;
    }

    /**
     * Gets the locator for the given selector.
     */
    private function guessLocator(string $selector, ?string $value = null): Locator
    {
        return (new GuessLocator($this->page, $this->scope))->for($selector, $value);
    }

    /**
     * Gets the locator for the given text.
     */
    private function getTextLocator(string $text): Locator
    {
        return $this->page->unstrict(
            fn (): Locator => $this->page->locator($this->scope)->getByText($text)
        );
    }
}
