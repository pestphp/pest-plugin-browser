<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Contracts\Operation;
use Pest\Browser\Operations\AssertUrlIs;
use Pest\Browser\Operations\Visit;
use Pest\Browser\Playwright\Client;
use RuntimeException;

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

        $title = $this->extractResultValue($response);

        // Assert that the title matches
        test()->assertSame(
            $expectedTitle,
            $title,
            sprintf('Failed asserting that page title "%s" matches expected "%s".', $title, $expectedTitle)
        );

        return $this;
    }

    /**
     * Extract a result value from a response.
     *
     * @param  iterable  $response  The response (array or Generator)
     * @return mixed The extracted value
     */
    private function extractResultValue(iterable $response)
    {
        // Get all values from keys matching *.result.value
        $values = [];
        foreach ($response as $item) {
            if (isset($item['result']['value'])) {
                $values[] = $item['result']['value'];
            }
        }

        // Filter out empty values
        $filteredValues = array_filter($values);

        // Get sole value (throw exception if not exactly one value)
        if (count($filteredValues) !== 1) {
            throw new RuntimeException('Expected exactly one result value.');
        }

        return reset($filteredValues);
    }
}
