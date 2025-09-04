<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

trait InteractsWithViewPort
{
    /**
     * Resizes the page.
     */
    public function resize(int $width, int $height): self
    {
        $this->page->setViewportSize($width, $height);

        return $this;
    }

    /**
     * Returns the viewport size.
     *
     * @return array{width: int, height: int}
     */
    public function getViewportSize(): array
    {
        return $this->page->viewportSize();
    }
}
