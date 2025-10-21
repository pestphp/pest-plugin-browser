<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

trait InteractsWithScreen
{
    /**
     * Performs a screenshot of the current page and saves it to the given path.
     */
    public function screenshot(bool $fullPage = true, ?string $filename = null): self
    {
        $filename = $this->getFilename($filename);

        $this->page->screenshot($fullPage, $filename);

        return $this;
    }

    /**
     * Performs a screenshot of an element and saves it to the given path.
     */
    public function screenshotElement(string $selector, ?string $filename = null): self
    {
        $filename = $this->getFilename($filename);

        $this->page->screenshotElement($selector, $filename);

        return $this;
    }

    /**
     * Get the filename for the screenshot.
     */
    private function getFilename(?string $filename = null): string
    {
        if ($filename === null) {
            // @phpstan-ignore-next-line
            return str_replace('__pest_evaluable_', '', test()->name());
        }

        return $filename;
    }
}
