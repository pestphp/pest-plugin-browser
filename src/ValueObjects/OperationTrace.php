<?php

declare(strict_types=1);

namespace Pest\Browser\ValueObjects;

use Pest\Support\Arr;

/**
 * @internal
 */
final readonly class OperationTrace
{
    public const TRACE_DEPTH = 4;

    /**
     * Construct a new instance
     *
     * @param  array<int, mixed>|null  $args
     */
    public function __construct(
        public string $file,
        public int $line,
        public string $method,
        public ?array $args = null,
    ) {
        //
    }

    /**
     * Create a new instance
     */
    public static function create(): self
    {
        $trace = debug_backtrace(0, self::TRACE_DEPTH)[self::TRACE_DEPTH - 1];

        return new self(
            Arr::get($trace, 'file', ''), // @phpstan-ignore-line
            Arr::get($trace, 'line', 0), // @phpstan-ignore-line
            Arr::get($trace, 'function', ''), // @phpstan-ignore-line
            Arr::get($trace, 'args'), // @phpstan-ignore-line
        );
    }
}
