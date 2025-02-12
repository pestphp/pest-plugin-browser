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

        $process->mustRun();

        $output = $process->getOutput();

        // @phpstan-ignore-next-line
        $outputAsArray = json_decode($output, true);

        return new TestResult($outputAsArray['suites'][0]['specs'][0]['ok']);
    }
}
