<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Exceptions\BrowserExpectationFailedException;
use Pest\Browser\Execution;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\ServerManager;
use PHPUnit\Framework\ExpectationFailedException;
use Throwable;

/**
 * @mixin Webpage
 */
final readonly class AwaitableWebpage
{
    /**
     * Creates a new awaitable webpage instance.
     *
     * @param  array<int, string>  $nonAwaitableMethods
     */
    public function __construct(
        private Page $page,
        private string $initialUrl,
        private array $nonAwaitableMethods = [
            'assertScreenshotMatches',
            'assertNoAccessibilityIssues',
        ],
    ) {
        //
    }

    /**
     * Awaits for the given method to assert true or fail.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $webpage = new Webpage($this->page, $this->initialUrl);

        try {
            if (
                in_array($name, $this->nonAwaitableMethods, true)
                || Playwright::timeout() <= 1000
            ) {
                // @phpstan-ignore-next-line
                $result = $webpage->{$name}(...$arguments);
            } else {
                $result = Execution::instance()->waitForExpectation(
                    // @phpstan-ignore-next-line
                    fn () => $webpage->{$name}(...$arguments),
                );
            }
        } catch (ExpectationFailedException $e) {
            ServerManager::instance()->http()->throwLastThrowableIfNeeded();

            try {
                $browserException = BrowserExpectationFailedException::from($this->page, $e);
            } catch (Throwable) { // @phpstan-ignore-line
                throw $e;
            }

            throw $browserException;
        }

        ServerManager::instance()->http()->throwLastThrowableIfNeeded();

        return $result === $webpage
            ? $this
            : $result;
    }

    /**
     * Return the page instance.
     */
    public function page(): Page
    {
        return $this->page;
    }
}
