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
     * Checks if a selector has a particular attribute.
     */
    public function assertAttribute(string $selector, string $attribute, string $value): self
    {
        $this->operations[] = new Operations\AssertAttribute($selector, $attribute, $value);

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
     * Checks if the URL scheme matches the given scheme.
     */
    public function assertSchemeIs(string $scheme): self
    {
        $this->operations[] = new Operations\AssertSchemeIs($scheme);

        return $this;
    }

    /**
     * Checks if the URL scheme does not match the given scheme.
     */
    public function assertSchemeIsNot(string $scheme): self
    {
        $this->operations[] = new Operations\AssertSchemeIsNot($scheme);

        return $this;
    }

    /**
     * Checks if the page URL matches the given host.
     */
    public function assertHostIs(string $host): self
    {
        $this->operations[] = new Operations\AssertHostIs($host);

        return $this;
    }

    /**
     * Checks if the page URL does not match the given host.
     */
    public function assertHostIsNot(string $host): self
    {
        $this->operations[] = new Operations\AssertHostIsNot($host);

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
     * Pauses the execution for a specified number of milliseconds.
     *
     * @param  int  $milliseconds  The number of milliseconds to pause. Default is 1000.
     */
    public function pause(int $milliseconds = 1000): self
    {
        if ($milliseconds <= 0) {
            throw new InvalidArgumentException('The number of milliseconds must be greater than 0.');
        }

        $this->operations[] = new Operations\Pause($milliseconds);

        return $this;
    }

    /**
     * Compile the JavaScript test file.
     */
    public function compile(): TestResult
    {
        $compiler = new Compiler($this->operations);

        $compiler->compile();

        $worker = new Worker;

        $result = $worker->run();

        expect($result->ok())->toBeTrue();

        return $result;
    }
}
