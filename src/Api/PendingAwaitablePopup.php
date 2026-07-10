<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Exceptions\BrowserExpectationFailedException;
use Pest\Browser\Execution;
use Pest\Browser\Playwright\Page;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @mixin AwaitableWebpage
 */
final class PendingAwaitablePopup
{
    /**
     * The webpage instance that will be returned when the popup is created.
     */
    private ?AwaitableWebpage $waitablePage = null;

    /**
     * Creates a new pending awaitable popup instance.
     */
    public function __construct(
        private readonly Page $opener,
    ) {
        //
    }

    /**
     * Calls the given method on page, waiting if needed for it to be created.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        if ($this->waitablePage instanceof AwaitableWebpage) {
            // @phpstan-ignore-next-line
            return $this->waitablePage->{$name}(...$arguments);
        }

        $result = Execution::instance()->waitForExpectation(function () use ($name, $arguments): mixed {
            if (is_null($this->waitablePage)) {
                $e = new ExpectationFailedException('No popup opened');
                throw BrowserExpectationFailedException::from($this->opener, $e);
            }

            // @phpstan-ignore-next-line
            return $this->waitablePage->{$name}(...$arguments);
        });

        return $result === $this->waitablePage
            ? $this
            : $result;
    }

    public function handlePopupCreation(string $popupGuid, string $frameGuid): void
    {
        $page = new Page($this->opener->context(), $popupGuid, $frameGuid);
        $this->waitablePage = new AwaitableWebpage($page, '(popup)');
    }
}
