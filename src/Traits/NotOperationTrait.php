<?php

declare(strict_types=1);

namespace Pest\Browser\Traits;

trait NotOperationTrait
{
    /**
     * Whether the assertion is negated.
     */
    private bool $not;

    public function initializeNot(bool $not = false): void
    {
        $this->not = $not;
    }

    protected function getNotSuffix(): string
    {
        return $this->not ? '.not' : '';
    }
}
