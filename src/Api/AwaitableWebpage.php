<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Exceptions\BrowserExpectationFailedException;
use Pest\Browser\Execution;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Playwright\Tracing;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\Step;
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
            // Retrying this action would append the value to what was already typed.
            'typeSlowly',
            // An attempt that times out after the page received the input would be
            // repeated: a second click closes the menu the first one opened, a second
            // "add row" adds two. Playwright already waits for the element itself.
            'click',
            'rightClick',
            'press',
            'pressAndWaitFor',
            'keys',
            'drag',
            'append',
            // A repeated navigation restarts the page load the previous attempt was waiting
            // for, so on a slow runner none of them ever finishes within an attempt. Playwright
            // already waits for the load state itself.
            'navigate',
            'refresh',
            'back',
            'forward',
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

        $call = in_array($name, $this->nonAwaitableMethods, true) || Playwright::timeout() <= 1000
            // @phpstan-ignore-next-line
            ? fn (): mixed => $webpage->{$name}(...$arguments)
            : fn (): mixed => Execution::instance()->waitForExpectation(
                // @phpstan-ignore-next-line
                fn () => $webpage->{$name}(...$arguments),
            );

        try {
            $tracing = $this->page->context()->tracing();

            if ($tracing instanceof Tracing && $tracing->path() !== null) {
                $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

                $result = $tracing->group(
                    Step::title($name, $arguments),
                    $caller['file'] ?? null,
                    $caller['line'] ?? null,
                    $call,
                );
            } else {
                $result = $call();
            }
        } catch (ExpectationFailedException $e) {
            ServerManager::instance()->http()->throwLastThrowableIfNeeded();

            try {
                $browserException = BrowserExpectationFailedException::from($this->page, $e);
            } catch (Throwable) {
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
