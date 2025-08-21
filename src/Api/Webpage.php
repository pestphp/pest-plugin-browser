<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use BadMethodCallException;
use Closure;
use Pest\Browser\Playwright\Locator;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Support\GuessLocator;
use Pest\Concerns\Extendable;

final class Webpage
{
    use Concerns\InteractsWithElements,
        Concerns\InteractsWithTab,
        Concerns\InteractsWithToolbar,
        Concerns\MakesConsoleAssertions,
        Concerns\MakesElementAssertions,
        Concerns\MakesScreenshotAssertions,
        Concerns\MakesUrlAssertions,
        Extendable;

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
     * Dynamically handle calls to the class.
     */
    public function __call(string $name, array $arguments)
    {
        if (! self::hasExtend($name)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', self::class, $name
            ));
        }

        $macro = self::$extends[$name];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, self::class);
        }

        return $macro(...$arguments);
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
     * Performs a screenshot of the current page and saves it to the given path.
     */
    public function screenshot(bool $fullPage = true, ?string $name = null): self
    {
        $name = is_string($name) ? $name : date('Y_m_d_H_i_s_u');

        $this->page->screenshot($fullPage, $name);

        return $this;
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

    /**
     * Gets the locator for the given selector.
     */
    private function guessLocator(string $selector, ?string $value = null): Locator
    {
        return (new GuessLocator($this->page))->for($selector, $value);
    }
}
