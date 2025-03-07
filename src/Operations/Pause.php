<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use InvalidArgumentException;

trait Pause
{
    /**
     * Sleep for the specified duration.
     */
    public function pause(int $milliseconds): self
    {
        if ($milliseconds < 0) {
            throw new InvalidArgumentException('Pause duration must be a non-negative value.');
        }

        if ($milliseconds > 0) {
            usleep($milliseconds * 1000);
        }

        return $this;
    }
}
