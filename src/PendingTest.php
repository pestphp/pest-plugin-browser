<?php

declare(strict_types=1);

namespace Pest\Browser;

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
     * Click the element at the given selector.
     */
    public function click(string $selector): self
    {
        $this->operations[] = new Operations\Click($selector);

        return $this;
    }

    /**
     * Perform a mouse click and hold the mouse button down at the given selector.
     */
    public function clickAndHold(string $selector, int $duration = 1000): self
    {
        $this->operations[] = new Operations\ClickAndHold($selector, $duration);

        return $this;
    }

    /**
     * Click the topmost element at the given pair of coordinates.
     */
    public function clickAtPoint(int $x = 0, int $y = 0): self
    {
        $this->operations[] = new Operations\ClickAtPoint($x, $y);

        return $this;
    }

    /**
     * Click the element at the given XPath expression.
     */
    public function clickAtXPath(string $expression): self
    {
        $this->operations[] = new Operations\ClickAtXPath($expression);

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
     * Control click the element at the given selector.
     */
    public function controlClick(string $selector): self
    {
        $this->operations[] = new Operations\ControlClick($selector);

        return $this;
    }

    /**
     * Control click the element at the given selector.
     */
    public function doubleClick(string $selector): self
    {
        $this->operations[] = new Operations\DoubleClick($selector);

        return $this;
    }

    /**
     * Right click the element at the given selector.
     */
    public function rightClick(string $selector): self
    {
        $this->operations[] = new Operations\RightClick($selector);

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
