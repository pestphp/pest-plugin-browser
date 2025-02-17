<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class AssertScript implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $expression,
        private string|bool|int|float|array|null $expected = true,
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $expression = explode(';', $this->expression)[0];
        $expected = $this->expected;

        if (is_bool($expected)) {
            $expected = $expected ? 'true' : 'false';
        } elseif (is_string($expected)) {
            $expected = str_replace("'", "\'", $expected);
            $expected = "'{$expected}'";
        } elseif (is_array($expected)) {
            $expected = json_encode($expected, JSON_THROW_ON_ERROR & JSON_PRETTY_PRINT);
        } elseif (is_null($expected)) {
            $expected = 'null';
        }

        return "await expect(await page.evaluate(() => {$expression})).toStrictEqual({$expected});";
    }
}
