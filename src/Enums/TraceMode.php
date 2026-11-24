<?php

declare(strict_types=1);

namespace Pest\Browser\Enums;

/**
 * @internal
 */
enum TraceMode: string
{
    case OFF = 'off';
    case ON = 'on';
    case RETAIN_ON_FAILURE = 'retain-on-failure';

    /**
     * Whether tracing is enabled for this mode.
     */
    public function isEnabled(): bool
    {
        return $this !== self::OFF;
    }

    /**
     * Whether a recorded trace should be kept for the given test outcome.
     */
    public function shouldSave(bool $failed): bool
    {
        return match ($this) {
            self::OFF => false,
            self::ON => true,
            self::RETAIN_ON_FAILURE => $failed,
        };
    }
}
