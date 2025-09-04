<?php

declare(strict_types=1);

namespace Pest\Browser\Enums;

/**
 * @internal
 */
enum AccessibilityIssueLevel: string
{
    case LevelZero = 'critical';
    case LevelOne = 'serious';
    case LevelTwo = 'moderate';
    case LevelThree = 'minor';

    /**
     * Create an AccessibilityIssueLevel from a numeric level.
     */
    public static function tryFromLevel(int $level): ?self
    {
        return match ($level) {
            0 => self::LevelZero,
            1 => self::LevelOne,
            2 => self::LevelTwo,
            3 => self::LevelThree,
            default => null,
        };
    }

    /**
     * Get the numeric level of the accessibility issue.
     */
    public function level(): int
    {
        return match ($this) {
            self::LevelZero => 0,
            self::LevelOne => 1,
            self::LevelTwo => 2,
            self::LevelThree => 3,
        };
    }
}
