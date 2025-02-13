<?php

namespace Pest\Browser\Traits;

trait StrOrRegExp
{
    protected function strOrRegExp(string $value): string
    {
        return str_starts_with($value, '/')
            ? $value
            : sprintf("'%s'", $value);
    }
}
