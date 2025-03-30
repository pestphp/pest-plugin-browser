<?php

declare(strict_types=1);

namespace Pest\Browser\Conditions;

use Pest\Browser\Contracts\Condition;

final readonly class See implements Condition
{
    /**
     * Creates a condition instance.
     */
    public function __construct(private string $text)
    {
        //
    }

    /**
     * Compile the condition.
     */
    public function compile(): string
    {
        $escapedText = json_encode($this->text);

        return sprintf('(await page.locator(\'body\').textContent()).includes(%s)', $escapedText);
    }
}
