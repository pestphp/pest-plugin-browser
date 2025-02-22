<?php

declare(strict_types=1);

namespace Pest\Browser\ValueObjects;

use Pest\Support\Arr;
use RuntimeException;
use SplFileObject;

/**
 * @internal
 */
final readonly class TestResult
{
    /**
     * Constructs a new instance.
     */
    public function __construct(
        public bool $isPassed,
        public string $browser,
        public ?string $errorLine = null,
        public mixed $actualValue = null,
        public mixed $expectedValue = null,
    ) {
        //
    }

    /**
     * Creates a new instance from an array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $isPassed = (bool) Arr::get($data, 'suites.0.specs.0.ok', false);
        $browser = (string) Arr::get($data, 'suites.0.specs.0.tests.0.projectName', ''); // @phpstan-ignore-line
        $errorLine = null;
        $actualValue = null;
        $expectedValue = null;

        if (! $isPassed) {
            $errorLocation = (array) Arr::get($data, 'suites.0.specs.0.tests.0.results.0.errorLocation', []);

            $errorLine = self::getErrorLine(
                (string) Arr::get($errorLocation, 'file'), // @phpstan-ignore-line
                (int) Arr::get($errorLocation, 'line') // @phpstan-ignore-line
            );

            if (Arr::has($data, 'suites.0.specs.0.tests.0.results.0.error.matcherResult.actual')) {
                $actualValue = Arr::get($data, 'suites.0.specs.0.tests.0.results.0.error.matcherResult.actual');
            }

            if (Arr::has($data, 'suites.0.specs.0.tests.0.results.0.error.matcherResult.expected')) {
                $expectedValue = Arr::get($data, 'suites.0.specs.0.tests.0.results.0.error.matcherResult.expected');
            }
        }

        return new self(
            $isPassed,
            $browser,
            $errorLine,
            $actualValue,
            $expectedValue,
        );
    }

    /**
     * Creates a new instance from JSON.
     */
    public static function fromJson(string $json): self
    {
        return self::fromArray(json_decode($json, true)); // @phpstan-ignore-line
    }

    /**
     * Get the error line for a given file and line number.
     */
    private static function getErrorLine(string $filePath, int $lineNumber): string
    {
        if (! file_exists($filePath)) {
            throw new RuntimeException("Test file not found: {$filePath}");
        }

        $file = new SplFileObject($filePath);
        $file->seek($lineNumber - 1);

        $content = $file->current();

        if (! is_string($content)) {
            throw new RuntimeException("Failed to read file at {$filePath}:{$lineNumber}");
        }

        return trim($content);
    }
}
