<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\Browser\Playwright\Locator;
use Pest\Browser\Playwright\Page;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @internal
 */
final readonly class GuessLocator
{
    /**
     * Creates a new guess locator instance.
     */
    public function __construct(
        private Page $page,
    ) {
        //
    }

    /**
     * Guesses the locator for the given page and selector.
     */
    public function for(string $selector, ?string $value = null): Locator
    {
        if (Selector::isExplicit($selector)) {
            if ($value !== null) {
                $selector .= sprintf('[value=%s]', Selector::escapeForAttributeSelectorOrRegex($value, true));
            }

            return $this->page->locator($selector);
        }

        foreach (['[id="%s"]', '[name="%s"]'] as $format) {
            $formattedSelector = sprintf($format, $selector);

            if ($value !== null) {
                $formattedSelector .= sprintf('[value=%s]', Selector::escapeForAttributeSelectorOrRegex($value, true));
            }

            $locator = $this->page->unstrict(
                fn (): Locator => $this->page->locator($formattedSelector),
            );

            if ($locator->count() > 0) {
                return $locator;
            }
        }

        if ($value !== null) {
            return throw new ExpectationFailedException(
                sprintf('Selector [%s] does not match any element.', $selector)
            );
        }

        return $this->page->unstrict(
            fn (): Locator => $this->page->getByText($selector),
        );
    }
}
