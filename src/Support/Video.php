<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\TestSuite;

/**
 * @internal
 */
final class Video
{
    /**
     * Return the path to the videos directory.
     */
    public static function dir(): string
    {
        return TestSuite::getInstance()->rootPath
            .'/tests/Browser/Videos';
    }
}
