<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\ValueObjects\TestResult;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class Worker
{
    /**
     * Run the worker.
     */
    public function run(): TestResult
    {
        $process = new Process(['npx', 'playwright', 'test', '--reporter=json']);

        $process->run();

        $output = $process->getOutput();

        return TestResult::fromJson($output);
    }
}
