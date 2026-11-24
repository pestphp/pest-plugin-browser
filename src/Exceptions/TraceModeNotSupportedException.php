<?php

declare(strict_types=1);

namespace Pest\Browser\Exceptions;

use InvalidArgumentException;
use NunoMaduro\Collision\Contracts\RenderlessEditor;
use NunoMaduro\Collision\Contracts\RenderlessTrace;

/**
 * @internal
 */
final class TraceModeNotSupportedException extends InvalidArgumentException implements RenderlessEditor, RenderlessTrace
{
    //
}
