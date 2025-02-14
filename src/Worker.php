<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\ValueObjects\TestResult;
use Pest\Browser\ValueObjects\TestResultResponse;
use Pest\Support\Arr;
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

        $ok = Arr::get($outputAsArray, 'suites.0.specs.0.ok');
        $annotations = Arr::get($outputAsArray, 'suites.0.specs.0.tests.0.annotations');

        return new TestResult($ok, new TestResultResponse($annotations));
    }
}
