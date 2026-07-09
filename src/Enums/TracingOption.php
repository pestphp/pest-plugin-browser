<?php

declare(strict_types=1);

namespace Pest\Browser\Enums;

/**
 * @internal
 */
enum TracingOption: string
{
    case OFF = 'off';
    case ON = 'on';
    case RETAIN_ON_FAILURE = 'retain-on-failure';
}
