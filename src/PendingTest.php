<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Contracts\Operation;
use Pest\Browser\Operations\AssertUrlIs;
use Pest\Browser\Operations\Visit;
use Pest\Browser\Playwright\Client;

/**
 * @internal
 */
final class PendingTest
{
    use AssertUrlIs, Visit;

    /**
     * The pending operations.
     *
     * @var array<int, Operation>
     */
    private array $operations = []; // @phpstan-ignore-line

    /**
     * Checks if the page has a title.
     */
    public function assertTitle(string $expectedTitle): self
    {
        $response = Client::instance()->execute(
            $this->page->frame->guid,
            'title',
        );

        $title = fluent($response)
            ->collect('*.result.value')
            ->filter()
            ->sole();

        // Add expectation to a list?

        return $this;
    }
}
