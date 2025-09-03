<?php

declare(strict_types=1);

namespace Pest\Browser;

abstract class Page
{
    use PageConfiguration;

    /**
     * Get the URL for the page.
     */
    abstract public function url(): string;
}
