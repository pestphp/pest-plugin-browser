<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

final class TempDirectory
{
    public static function path(string $filename): string
    {
        return dirname(__DIR__, 2).'/.temp/'.$filename;
    }
}
