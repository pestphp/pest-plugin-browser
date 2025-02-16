<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\ValueObjects\TestResult;
use Pest\Support\Arr;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class Worker
{
    /**
     * Creates a new Worker instance.
     */
    public function __construct(
        private array $browsers = ['chrome'],
    ) {
        //
    }

    /**
     * Run the worker.
     */
    public function run(): TestResult
    {
        $browsers = array_map(fn ($browser) => "--project={$browser}", $this->browsers);

        $process = new Process(['npx', 'playwright', 'test', '--reporter=json', ...$browsers]);

        $process->mustRun();

        $output = $process->getOutput();

        /** @var array<int, mixed> $outputAsArray */
        $outputAsArray = json_decode($output, true);

        $ok = (bool) Arr::get($outputAsArray, 'suites.0.specs.0.ok');

        return new TestResult($ok);
    }
}
