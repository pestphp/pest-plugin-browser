<?php

declare(strict_types=1);

namespace Pest\Browser\Traits;

trait NotOperationTrait
{
    /**
     * Get the not operation.
     *
     * @return string
     */
    protected function isNotOperation(): string
    {
        return $this->not ? '.not' : '';
    }
}
