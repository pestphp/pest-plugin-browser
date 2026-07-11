<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\Browser\Execution;
use Pest\Browser\Playwright\Locator;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Playwright\Playwright;
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
        if (($locator = $this->explicitLocator($selector, $value)) instanceof Locator) {
            return $locator;
        }

        if (($locator = $this->dataTestLocator($selector)) instanceof Locator) {
            return $locator;
        }

        foreach (['[id="%s"]', '[name="%s"]'] as $format) {
            $formattedSelector = sprintf($format, $selector).$this->valueSelector($value);

            $locator = $this->page->unstrict(
                fn (): Locator => $this->page->locator($formattedSelector),
            );

            if ($locator->count() > 0) {
                return $locator;
            }
        }

        if ($value !== null) {
            throw new ExpectationFailedException(
                sprintf('Selector [%s] does not match any element.', $selector),
            );
        }

        return $this->page->unstrict(
            fn (): Locator => $this->page->getByText($selector, true),
        );
    }

    public function forClickable(string $selector): Locator
    {
        if (($locator = $this->explicitLocator($selector)) instanceof Locator) {
            return $locator;
        }

        if (($locator = $this->dataTestLocator($selector)) instanceof Locator) {
            return $locator;
        }

        return $this->resolveLocator($selector, 'clickable element', [
            fn (): Locator => $this->page->locator(sprintf('[id="%s"]', $selector)),
            fn (): Locator => $this->page->locator(sprintf('[name="%s"]', $selector)),
            fn (): Locator => $this->page->getByRole('button', [
                'name' => $selector,
                'exact' => true,
            ]),
            fn (): Locator => $this->page->getByRole('link', [
                'name' => $selector,
                'exact' => true,
            ]),
            fn (): Locator => $this->page->getByText($selector, true),
        ]);
    }

    public function forField(string $selector): Locator
    {
        if (($locator = $this->explicitLocator($selector)) instanceof Locator) {
            return $locator;
        }

        if (($locator = $this->dataTestLocator($selector)) instanceof Locator) {
            return $locator;
        }

        return $this->resolveLocator($selector, 'field control', [
            fn (): Locator => $this->page->locator($this->fieldSelector('id', $selector)),
            fn (): Locator => $this->page->locator($this->fieldSelector('name', $selector)),
            fn (): Locator => $this->page->getByLabel($selector, true),
            fn (): Locator => $this->page->getByPlaceholder($selector, true),
        ]);
    }

    public function forCheckable(string $selector, ?string $value = null): Locator
    {
        if (($locator = $this->explicitLocator($selector, $value)) instanceof Locator) {
            return $locator;
        }

        if (($locator = $this->dataTestLocator($selector, $value)) instanceof Locator) {
            return $locator;
        }

        $candidates = [
            fn (): Locator => $this->page->locator($this->checkableSelector('id', $selector, $value)),
            fn (): Locator => $this->page->locator($this->checkableSelector('name', $selector, $value)),
            fn (): Locator => $this->page->getByLabel($selector, true),
        ];

        if ($value === null) {
            $candidates[] = fn (): Locator => $this->page->getByRole('checkbox', [
                'name' => $selector,
                'exact' => true,
            ]);
            $candidates[] = fn (): Locator => $this->page->getByRole('radio', [
                'name' => $selector,
                'exact' => true,
            ]);
        }

        return $this->resolveLocator($selector, 'checkable control', $candidates, $value);
    }

    public function forSelectable(string $selector): Locator
    {
        if (($locator = $this->explicitLocator($selector)) instanceof Locator) {
            return $locator;
        }

        if (($locator = $this->dataTestLocator($selector)) instanceof Locator) {
            return $locator;
        }

        return $this->resolveLocator($selector, 'selectable control', [
            fn (): Locator => $this->page->locator(sprintf('select[id="%s"]', $selector)),
            fn (): Locator => $this->page->locator(sprintf('select[name="%s"]', $selector)),
            fn (): Locator => $this->page->getByLabel($selector, true),
        ]);
    }

    /**
     * @param  array<int, callable(): Locator>  $candidates
     */
    private function resolveLocator(
        string $selector,
        string $category,
        array $candidates,
        ?string $value = null,
    ): Locator {
        $locator = $this->retryUntilTimeout(
            /**
             * @throws ExpectationFailedException
             */
            function () use ($candidates): Locator {
                foreach ($candidates as $candidate) {
                    $locator = $this->page->unstrict(
                        fn (): Locator => $candidate(),
                    );

                    if ($this->locatorExists($locator)) {
                        return $locator;
                    }
                }

                throw new ExpectationFailedException('No candidate matched during this retry cycle.');
            },
        );

        if ($locator instanceof Locator) {
            return $locator;
        }

        throw new ExpectationFailedException($this->missingLocatorMessage($selector, $category, $value));
    }

    /**
     * @param  callable(): Locator  $callback
     */
    private function retryUntilTimeout(callable $callback): ?Locator
    {
        $end = microtime(true) + (Playwright::timeout() / 1_000);

        do {
            try {
                return $callback();
            } catch (ExpectationFailedException) {
                Execution::instance()->tick();
            }
        } while (microtime(true) < $end);

        return null;
    }

    private function locatorExists(Locator $locator): bool
    {
        return Playwright::usingTimeout(1, fn (): bool => $locator->count() > 0);
    }

    private function explicitLocator(string $selector, ?string $value = null): ?Locator
    {
        if (! Selector::isExplicit($selector)) {
            return null;
        }

        return $this->page->locator($selector.$this->valueSelector($value));
    }

    private function dataTestLocator(string $selector, ?string $value = null): ?Locator
    {
        if (! Selector::isDataTest($selector)) {
            return null;
        }

        $id = Selector::escapeForAttributeSelectorOrRegex(str_replace('@', '', $selector), true);

        return $this->page->unstrict(
            fn (): Locator => $this->page->locator(
                sprintf(
                    ':is([data-testid=%1$s], [data-test=%1$s])%2$s',
                    $id,
                    $this->valueSelector($value),
                ),
            ),
        );
    }

    private function fieldSelector(string $attribute, string $selector): string
    {
        return sprintf(
            ':is(input, textarea, [contenteditable]:not([contenteditable="false"]))[%s="%s"]',
            $attribute,
            $selector,
        );
    }

    private function checkableSelector(string $attribute, string $selector, ?string $value = null): string
    {
        return sprintf(
            ':is(input[type="checkbox"], input[type="radio"])[%s="%s"]%s',
            $attribute,
            $selector,
            $this->valueSelector($value),
        );
    }

    private function valueSelector(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return sprintf('[value=%s]', Selector::escapeForAttributeSelectorOrRegex($value, true));
    }

    private function missingLocatorMessage(string $selector, string $category, ?string $value = null): string
    {
        if ($value === null) {
            return sprintf('No suitable %s was found for [%s].', $category, $selector);
        }

        return sprintf('No suitable %s was found for [%s] with value [%s].', $category, $selector, $value);
    }
}
