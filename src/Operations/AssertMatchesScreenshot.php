<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;
use Pest\TestSuite;

/**
 * @internal description
 */
final readonly class AssertMatchesScreenshot implements Operation
{
    /**
     * The path to already present screenshot.
     */
    private string $path;

    /**
     * Creates an operation instance.
     */
    public function __construct(string $filename)
    {
        $basePath = TestSuite::getInstance()->testPath.'/Browser/screenshots';

        $this->path = $basePath.'/'.$filename;
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        return "await expect(page).toHaveScreenshot('$this->path')";
    }
}
