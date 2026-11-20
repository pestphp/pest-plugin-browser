<?php

declare(strict_types=1);

namespace Pest\Browser\Enums;

/**
 * @internal
 */
enum TraceMode: string
{
    case On = 'on';
    case RetainOnFailure = 'retain-on-failure';

    /**
     * Whether a recorded trace should be kept for the given test outcome.
     */
    public function shouldSave(bool $failed): bool
    {
        return match ($this) {
            self::On => true,
            self::RetainOnFailure => $failed,
        };
    }
}
