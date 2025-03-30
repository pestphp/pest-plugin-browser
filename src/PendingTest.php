<?php

declare(strict_types=1);

namespace Pest\Browser;

use Closure;
use InvalidArgumentException;
use Pest\Browser\Contracts\Condition;
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
    public array $operations = [];

    /**
     * Creates a new pending test instance.
     */
    public function __construct(private readonly bool $compileTest = true) {}

    /**
     * Ends the chain and builds the test result.
     */
    public function __destruct()
    {
        if (! $this->compileTest) {
            return;
        }

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
     * Determines if the screenshot matches stored screenshot
     */
    public function assertMatchesScreenshot(string $filename): self
    {
        $this->operations[] = new Operations\AssertMatchesScreenshot($filename);

        return $this;
    }

    /**
     * Determines if the screenshot doesn't match stored screenshot
     */
    public function assertNotMatchesScreenshot(string $filename): self
    {
        $this->operations[] = new Operations\AssertNotMatchesScreenshot($filename);

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
     * Checks if a selector has a particular attribute, with a specific value.
     */
    public function assertAttribute(string $selector, string $attribute, string $value): self
    {
        $this->operations[] = new Operations\AssertAttribute($selector, $attribute, $value);

        return $this;
    }

    /**
     * Checks if a selector has a particular attribute that contains a specific value.
     */
    public function assertAttributeContains(string $selector, string $attribute, string $value): self
    {
        $this->operations[] = new Operations\AssertAttributeContains($selector, $attribute, $value);

        return $this;
    }

    /**
     * Checks if a selector does not have a particular attribute.
     */
    public function assertAttributeMissing(string $selector, string $attribute): self
    {
        $this->operations[] = new Operations\AssertAttributeMissing($selector, $attribute);

        return $this;
    }

    /**
     * Checks if a selector has a particular attribute that doesn't contain a specific value.
     */
    public function assertAttributeDoesntContain(string $selector, string $attribute, string $value): self
    {
        $this->operations[] = new Operations\AssertAttributeDoesntContain($selector, $attribute, $value);

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
     * Checks if the page has a specific element.
     */
    public function assertPresent(string $selector): self
    {
        $this->operations[] = new Operations\AssertPresent($selector);

        return $this;
    }

    /**
     * Checks if the page does not have a specific element.
     */
    public function assertNotPresent(string $selector): self
    {
        $this->operations[] = new Operations\AssertNotPresent($selector);

        return $this;
    }

    /**
     * Checks if the page url has the given query string.
     */
    public function assertQueryStringHas(string $name, ?string $value = null): self
    {
        $this->operations[] = new Operations\AssertQueryStringHas($name, $value);

        return $this;
    }

    /**
     * Checks if the page url does not have the given query string.
     */
    public function assertQueryStringMissing(string $name, ?string $value = null): self
    {
        $this->operations[] = new Operations\AssertQueryStringMissing($name, $value);

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
     * Checks if the page URL begins with the given path.
     */
    public function assertPathBeginsWith(string $path): self
    {
        $this->operations[] = new Operations\AssertPathBeginsWith($path);

        return $this;
    }

    /**
     * Checks if the page URL ends with the given path.
     */
    public function assertPathEndsWith(string $path): self
    {
        $this->operations[] = new Operations\AssertPathEndsWith($path);

        return $this;
    }

    /**
     * Checks if the page URL contains the given path.
     */
    public function assertPathContains(string $path): self
    {
        $this->operations[] = new Operations\AssertPathContains($path);

        return $this;
    }

    /**
     * Checks if the page URL path matches the given path.
     * The asterisk (*) character can be used as a wildcard.
     */
    public function assertPathIs(string $path): self
    {
        $this->operations[] = new Operations\AssertPathIs($path);

        return $this;
    }

    /**
     * Checks if the page URL path does not match the given path.
     */
    public function assertPathIsNot(string $path): self
    {
        $this->operations[] = new Operations\AssertPathIsNot($path);

        return $this;
    }

    /**
     * Checks if the given script returns the expected value.
     *
     * @param  array<mixed>|bool|float|int|string|null  $expected
     */
    public function assertScript(string $expression, array|bool|float|int|null|string $expected): self
    {
        $this->operations[] = new Operations\AssertScript($expression, $expected);

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
     * Sets the timeout for the test.
     *
     * @param  int  $milliseconds  The number of milliseconds to wait before timing out. Default is 30000.
     */
    public function setTimeout(int $milliseconds = 30000): self
    {
        if ($milliseconds <= 0) {
            throw new InvalidArgumentException('The number of milliseconds must be greater than 0.');
        }

        array_unshift($this->operations, new Operations\SetTimeout($milliseconds));

        return $this;
    }

    /**
     * Checks if the given element is visible.
     */
    public function assertVisible(string $selector): self
    {
        $this->operations[] = new Operations\AssertVisible($selector);

        return $this;
    }

    /**
     * Checks if the given element is not visible.
     */
    public function assertMissing(string $selector): self
    {
        $this->operations[] = new Operations\AssertMissing($selector);

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
     * Checks if the given element is checked.
     */
    public function assertChecked(string $selector): self
    {
        $this->operations[] = new Operations\AssertChecked($selector);

        return $this;
    }

    /**
     * Double-click the element at the given selector.
     */
    public function doubleClick(string $selector): self
    {
        $this->operations[] = new Operations\DoubleClick($selector);

        return $this;
    }

    /**
     * Checks if the given element is not checked.
     */
    public function assertNotChecked(string $selector): self
    {
        $this->operations[] = new Operations\AssertNotChecked($selector);

        return $this;
    }

    /**
     * Right-click the element at the given selector.
     */
    public function rightClick(string $selector): self
    {
        $this->operations[] = new Operations\RightClick($selector);

        return $this;
    }

    /**
     * Check the given element.
     */
    public function check(string $selector): self
    {
        $this->operations[] = new Operations\Check($selector);

        return $this;
    }

    /**
     * Uncheck the given element.
     */
    public function uncheck(string $selector): self
    {
        $this->operations[] = new Operations\UnCheck($selector);

        return $this;
    }

    /**
     * Adds a "when" condition to perform an action based on the condition evaluation.
     *
     * This method registers a condition to be checked. If the condition is met, the provided
     * `$then` callback is executed. If the condition is not met, the optional `$else` callback
     * can be executed (if provided).
     *
     * @param  Condition  $condition  The condition to evaluate.
     * @param  Closure  $then  The callback to execute when the condition is true.
     * @param  Closure|null  $else  The callback to execute when the condition is false. Default is null.
     * @return self Returns the current instance to allow method chaining.
     */
    public function when(Condition $condition, Closure $then, ?Closure $else = null): self
    {
        $this->operations[] = new Operations\When($condition, $then, $else);

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
