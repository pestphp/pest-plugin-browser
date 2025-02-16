<?php

declare(strict_types=1);

namespace Pest\Browser;

use InvalidArgumentException;
use Pest\Browser\Contracts\Operation;
use Pest\Browser\ValueObjects\TestResult;

/**
 * @internal
 */
final class PendingTest
{
    /**
     * The list of supported browsers.
     */
    private const SUPPORTED_BROWSERS = ['chrome', 'firefox', 'safari'];

    /**
     * The list of browsers for the test.
     */
    private array $browsers = ['chrome'];

    /**
     * The pending operations.
     *
     * @var array<int, Operation>
     */
    private array $operations = [];

    /**
     * Ends the chain and builds the test result.
     */
    public function __destruct()
    {
        $this->compile();
    }

    /**
     * Sets the browsers for the test.
     */
    public function withBrowser(array ...$browsers): self
    {
        $browsers = array_filter(
            $browsers,
            fn ($browser) => in_array($browser, self::SUPPORTED_BROWSERS)
        );

        if (! $browsers) {
            throw new InvalidArgumentException(
                'At least one supported browser (chrome, firefox, safari) must be provided.'
            );
        }

        $this->browsers = $browsers;

        return $this;
    }

    /**
     * Visits a URL.
     */
    public function visit(string $url): self
    {
        $this->operations[] = new Operations\Visit($url);

        return $this;
    }

    /**
     * Goes back.
     */
    public function back(): self
    {
        $this->operations[] = new Operations\Back();

        return $this;
    }

    /**
     * Goes forward.
     */
    public function forward(): self
    {
        $this->operations[] = new Operations\Forward();

        return $this;
    }

    /**
     * Refreshes.
     */
    public function refresh(): self
    {
        $this->operations[] = new Operations\Refresh();

        return $this;
    }

    /**
     * Takes a screenshot.
     */
    public function screenshot(?string $path = null): self
    {
        $this->operations[] = new Operations\Screenshot($path);

        return $this;
    }

    /**
     * Checks if the page has a title.
     */
    public function assertTitle(string $title): self
    {
        $this->operations[] = new Operations\AssertTitle($title);

        return $this;
    }

    /**
     * Checks if the page has a title that contains the given text.
     */
    public function assertTitleContains(string $text): self
    {
        $this->operations[] = new Operations\AssertTitleContains($text);

        return $this;
    }

    /**
     * Checks if the page contains the given text.
     */
    public function assertSee(string $text, bool $ignoreCase = false): self
    {
        $this->operations[] = new Operations\AssertSee($text, $ignoreCase);

        return $this;
    }

    /**
     * Checks if the page does not contain the given text.
     */
    public function assertDontSee(string $text, bool $ignoreCase = false): self
    {
        $this->operations[] = new Operations\AssertDontSee($text, $ignoreCase);

        return $this;
    }

    /**
     * Checks if the page has a URL.
     */
    public function assertUrlIs(string $url): self
    {
        $this->operations[] = new Operations\AssertUrlIs($url);

        return $this;
    }

    /**
     * Clicks some text on the page.
     */
    public function clickLink(string $text, string $selector = 'a'): self
    {
        $this->operations[] = new Operations\ClickLink($text, $selector);

        return $this;
    }

    /**
     * Compile the JavaScript test file.
     */
    public function compile(): TestResult
    {
        $compiler = new Compiler($this->operations);

        $compiler->compile();

        $worker = new Worker($this->browsers);

        $result = $worker->run();

        expect($result->ok())->toBeTrue();

        return $result;
    }
}
