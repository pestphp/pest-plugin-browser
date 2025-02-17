<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Support\Arr;
use SplFileObject;

/**
 * @internal
 */
final readonly class TestResult
{
    /**
     * The test result.
     */
    public function __construct(
        private array $result,
    ) {
        //
    }

    /**
     * Get the test result.
     */
    public function ok(): bool
    {
        return (bool) Arr::get($this->result, 'suites.0.specs.0.ok', false);
    }

    /**
     * Get the failed line.
     */
    public function getFailedLine(): ?string
    {
        if ($this->ok()) {
            return null;
        }

        $failedTest = $this->getFirstFailedTest();

        if (! $failedTest) {
            return null;
        }

        $failedTestResult = Arr::get($failedTest, 'results.0');
        $filePath = Arr::get($failedTestResult, 'errorLocation.file');
        $lineNumber = Arr::get($failedTestResult, 'errorLocation.line');

        $file = new SplFileObject($filePath);
        $file->seek($lineNumber - 1);

        return trim($file->current());
    }

    /**
     * Get the failed browser.
     */
    public function getFailedBrowser(): ?string
    {
        if ($this->ok()) {
            return null;
        }

        $failedTest = $this->getFirstFailedTest();

        return Arr::get($failedTest, 'projectName') ?: null;
    }

    /**
     * Get the first failed test.
     */
    private function getFirstFailedTest(): ?array
    {
        $tests = Arr::get($this->result, 'suites.0.specs.0.tests', []);

        $failedTests = array_filter(
            $tests,
            fn ($test) => Arr::get($test, 'status') === 'unexpected'
        );

        return reset($failedTests) ?: null;
    }
}
