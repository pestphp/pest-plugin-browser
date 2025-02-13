<?php

declare(strict_types=1);

namespace Pest\Browser;

/**
 * @internal
 */
final class PendingTest
{
    /**
     * The pending operations.
     */
    private array $operations = [];

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
     * Build the test result.
     */
    public function build(): void
    {
        $compiler = new Compiler($this->operations);

        $compiler->compile();

        $worker = new Worker;

        $result = $worker->run();

        expect($result->ok())->toBeTrue();
    }

    public function elementScreenshot(string $selector, string $path): self
    {
        $this->operations[] = new Operations\ElementScreenshot($selector, $path);

        return $this;
    }

    /**
     * Ends the chain and builds the test result.
     */
    public function __destruct()
    {
        $this->build();
    }
}
