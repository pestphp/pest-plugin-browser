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
     * Checks if the page has a title.
     */
    public function toHaveTitle(string $title): self
    {
        $this->operations[] = new Operations\ToHaveTitle($title);

        return $this;
    }

    /**
     * Clicks some text on the page.
     */
    public function clickLink(string $text): self
    {
        $this->operations[] = new Operations\ClickLink($text);

        return $this;
    }

    /**
     * Checks if the page url is matching.
     */
    public function assertUrlIs(string $url): self
    {
        $this->operations[] = new Operations\AssertUrlIs($url);

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
