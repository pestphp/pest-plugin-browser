<?php

declare(strict_types=1);

namespace Pest\Browser\Exceptions;

use NunoMaduro\Collision\Contracts\RenderableOnCollisionEditor;
use PHPUnit\Framework\AssertionFailedError;
use Whoops\Exception\Frame;

final class BrowserOperationException extends AssertionFailedError implements RenderableOnCollisionEditor
{
    /**
     * Creates a new exception instance.
     */
    public function __construct(string $message, string $file, int $line)
    {
        parent::__construct($message);

        $this->file = $file;
        $this->line = $line;
    }

    /**
     * {@inheritDoc}
     */
    public function toCollisionEditor(): Frame
    {
        return new Frame([
            'file' => $this->file,
            'line' => $this->line,
        ]);
    }
}
