<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Contracts\Operation as OperationContract;
use Pest\Browser\Exceptions\BrowserOperationException;
use Pest\Browser\ValueObjects\OperationTrace;
use Pest\Browser\ValueObjects\TestResult;

/**
 * @internal
 */
abstract readonly class Operation implements OperationContract
{
    /**
     * Trace of the operation call.
     */
    public OperationTrace $trace;

    /**
     * Construct a new instance.
     */
    public function __construct()
    {
        $this->trace = OperationTrace::create();
    }

    /**
     * {@inheritDoc}
     */
    abstract public function compile(): string;

    /**
     * {@inheritDoc}
     */
    final public function fail(TestResult $testResult): void
    {
        $message = sprintf(
            '[%s] Failed %s operation',
            $testResult->browser,
            $this->trace->method
        );

        if (is_array($this->trace->args)) {
            $message = sprintf(
                '%s with arguments: %s',
                $message,
                implode(', ', array_map(
                    fn (mixed $arg): string => is_string($arg) ? $arg : (string) json_encode($arg),
                    $this->trace->args
                ))
            );
        }

        if ($testResult->actualValue !== null || $testResult->expectedValue !== null) {
            $message .= sprintf(
                "\n  Expected: %s\n  Actual: %s",
                is_string($testResult->expectedValue) ? $testResult->expectedValue : json_encode($testResult->expectedValue),
                is_string($testResult->actualValue) ? $testResult->actualValue : json_encode($testResult->actualValue)
            );
        }

        throw new BrowserOperationException($message, $this->trace->file, $this->trace->line);
    }
}
