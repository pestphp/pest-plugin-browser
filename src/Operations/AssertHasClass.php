<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;
use Pest\Browser\Support\Str;

/**
 * @internal
 */
final readonly class AssertHasClass implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private string $locator,
        private string|array $class,
    ) {}

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $cssTarget = is_array($this->class)
            ? $this->formatClassArray($this->class)
            : $this->formatClass($this->class);

        return sprintf(
            "await expect(page.locator('%s')).toHaveClass(%s);",
            addslashes($this->locator),
            $cssTarget
        );
    }

    /**
     * Format class names for JavaScript assertion.
     */
    private function formatClass(string $class): string
    {
        return Str::isRegex($class) ? $class : sprintf("'%s'", addslashes($class));
    }

    /**
     * Format an array of class names for JavaScript assertion.
     */
    private function formatClassArray(array $classes): string
    {
        array_walk($classes, fn (&$class) => $class = $this->formatClass($class));

        return '['.implode(', ', $classes).']';
    }
}
