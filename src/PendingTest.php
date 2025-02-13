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
    public function toHaveTitle(string $title, bool $not = false): self
    {
        $this->operations[] = new Operations\ToHaveTitle($title, $not);

        return $this;
    }

    /**
     * Checks if the page doesn't have a title.
     */
    public function doesntHaveTitle(string $title): self
    {
        $this->operations[] = new Operations\DoesntHaveTitle($title);

        return $this;
    }

    /**
     * Checks if the page has a URL.
     */
    public function toHaveURL(string $url, bool $not = false): self
    {
        $this->operations[] = new Operations\ToHaveURL($url, $not);

        return $this;
    }

    /**
     * Checks if the page doesn't have a URL.
     */
    public function doesntHaveURL(string $url): self
    {
        $this->operations[] = new Operations\DoesntHaveURL($url);

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

    /**
     * Ends the chain and builds the test result.
     */
    public function __destruct()
    {
        $this->build();
    }
}
