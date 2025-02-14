<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Contracts\Operation;

/**
 * @internal
 */
final readonly class Compiler
{
    /**
     * The path to the tests.
     */
    private const TEST_PATH = __DIR__.'/../.temp/e2e/runtime.spec.js';

    /**
     * @param  array<int, Operation>  $operations
     */
    public function __construct(
        private array $operations
    ) {
        //
    }

    /**
     * Compiles the operations.
     */
    public function compile(): void
    {
        $content = implode(
            "\n",
            array_map(
                static fn (Operation $operation): string => "\t{$operation->compile()}",
                $this->operations,
            ),
        );

        file_put_contents(self::TEST_PATH, <<<JS
            import { test, expect } from '@playwright/test';

            test('runtime', async ({ page }) => {
            $content
            });
            JS,
        );
    }
}
