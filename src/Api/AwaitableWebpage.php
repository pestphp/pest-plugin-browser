<?php

declare(strict_types=1);

namespace Pest\Browser\Api;

use Pest\Browser\Playwright\Page;

/**
 * @mixin Webpage
 */
final readonly class AwaitableWebpage
{
    /**
     * Creates a new awaitable webpage instance.
     */
    public function __construct(
        private Page $page,
    ) {
        //
    }

    /**
     * Awaits for the given method to assert true or fail.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $webpage = new Webpage($this->page);

        $result = $this->page->await(
            // @phpstan-ignore-next-line
            fn () => $webpage->{$name}(...$arguments),
        );

        return $result === $webpage
            ? $this
            : $result;
    }
}
