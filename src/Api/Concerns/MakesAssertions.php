<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Illuminate\Support\Str;
use Pest\Browser\Api\Webpage;

/**
 * @mixin Webpage
 */
trait MakesAssertions
{
    /**
     * Assert that the page title matches the given text.
     */
    public function assertTitle(string $title): Webpage
    {
        expect($this->page->title())->toBe($title, "Expected page title to be '{$title}' but found '{$this->page->title()}'.");

        return $this;
    }

    /**
     * Assert that the page title contains the given text.
     */
    public function assertTitleContains(string $title): Webpage
    {
        $pageTitle = $this->page->title();
        $message = "Expected page title to contain '{$title}' but found '{$pageTitle}'.";
        expect(str_contains($pageTitle, $title))->toBeTrue($message);

        return $this;
    }

    /**
     * Assert that the given text is present on the page.
     */
    public function assertSee(string $text): Webpage
    {
        $locator = $this->page->unstrict(
            fn () => $this->page->getByText($text),
        );

        expect($locator->isVisible())->toBeTrue("Expected to see text '{$text}' on the page, but it was not found or not visible.");

        return $this;
    }

    /**
     * Assert that the given text is not present on the page.
     */
    public function assertDontSee(string $text): Webpage
    {
        $locator = $this->page->unstrict(
            fn () => $this->page->getByText($text),
        );

        expect($locator->count())->toBe(0, "Expected not to see text '{$text}' on the page, but it was found.");

        return $this;
    }

    /**
     * Assert that the given text is present within the selector.
     */
    public function assertSeeIn(string $selector, string $text): Webpage
    {
        $locator = $this->guessLocator($selector);

        expect($locator->getByText($text)->isVisible())->toBeTrue("Expected to see text '{$text}' within element '{$selector}', but it was not found or not visible.");

        return $this;
    }

    /**
     * Assert that the given text is not present within the selector.
     */
    public function assertDontSeeIn(string $selector, string $text): Webpage
    {
        $locator = $this->guessLocator($selector);

        expect($locator->getByText($text)->count())->toBe(0, "Expected not to see text '{$text}' within element '{$selector}', but it was found.");

        return $this;
    }

    /**
     * Assert that any text is present within the selector.
     */
    public function assertSeeAnythingIn(string $selector): Webpage
    {
        $text = $this->guessLocator($selector)->textContent();

        expect($text)->not->toBeEmpty("Expected element '{$selector}' to contain some text, but it was empty.");

        return $this;
    }

    /**
     * Assert that no text is present within the selector.
     */
    public function assertSeeNothingIn(string $selector): Webpage
    {
        $text = $this->guessLocator($selector)->textContent();

        expect($text)->toBeEmpty("Expected element '{$selector}' to be empty, but it contained text: '{$text}'.");

        return $this;
    }

    /**
     * Assert that a given element is present a given amount of times.
     */
    public function assertCount(string $selector, int $expected): Webpage
    {
        $count = $this->guessLocator($selector)->count();
        expect($count)->toBe($expected, "Expected to find {$expected} elements matching '{$selector}', but found {$count}.");

        return $this;
    }

    /**
     * Assert that the given JavaScript expression evaluates to the given value.
     */
    public function assertScript(string $expression, mixed $expected = true): Webpage
    {
        if (! Str::contains($expression, ['===', '!==', '==', '!=', '>', '<', '>=', '<=', '&&', '||']) && ! Str::startsWith($expression, 'return ') && ! Str::startsWith($expression, 'function')) {
            $expression = "function() { return {$expression}; }";
        }

        $result = $this->page->evaluate($expression);

        if (is_bool($expected)) {
            $expectedStr = $expected ? 'true' : 'false';
        } elseif (is_string($expected)) {
            $expectedStr = "'{$expected}'";
        } elseif (is_scalar($expected) || is_null($expected)) {
            $expectedStr = (string) $expected;
        } else {
            $expectedStr = gettype($expected);
        }

        // Format result value for display
        if (is_bool($result)) {
            $resultStr = $result ? 'true' : 'false';
        } elseif (is_string($result)) {
            $resultStr = "'{$result}'";
        } elseif (is_scalar($result) || is_null($result)) {
            $resultStr = (string) $result;
        } else {
            $resultStr = gettype($result);
        }

        expect($result)->toBe($expected, "Expected JavaScript expression '{$expression}' to evaluate to {$expectedStr}, but got {$resultStr}.");

        return $this;
    }

    /**
     * Assert that the given source code is present on the page.
     */
    public function assertSourceHas(string $code): Webpage
    {
        $content = $this->page->content();
        $message = "Expected page source to contain '{$code}', but it was not found.";
        expect(str_contains($content, $code))->toBeTrue($message);

        return $this;
    }

    /**
     * Assert that the given source code is not present on the page.
     */
    public function assertSourceMissing(string $code): Webpage
    {
        $content = $this->page->content();
        $message = "Expected page source not to contain '{$code}', but it was found.";
        expect(str_contains($content, $code))->toBeFalse($message);

        return $this;
    }

    /**
     * Assert that the given link is present on the page.
     */
    public function assertSeeLink(string $link): Webpage
    {
        $locator = $this->guessLocator($link);

        expect($locator->isVisible())->toBeTrue("Expected to see link with text '{$link}', but it was not found or not visible.");

        return $this;
    }

    /**
     * Assert that the given link is not present on the page.
     */
    public function assertDontSeeLink(string $link): Webpage
    {
        $locator = $this->guessLocator($link);

        expect($locator->count())->toBe(0, "Expected not to see link with text '{$link}', but it was found.");

        return $this;
    }

    /**
     * Assert that the given checkbox is checked.
     */
    public function assertChecked(string $field, ?string $value = null): Webpage
    {
        $valueDescription = $value !== null ? " with value '{$value}'" : '';
        expect($this->guessLocator($field, $value)->isChecked())->toBeTrue("Expected checkbox '{$field}'{$valueDescription} to be checked, but it was not.");

        return $this;
    }

    /**
     * Assert that the given checkbox is not checked.
     */
    public function assertNotChecked(string $field, ?string $value = null): Webpage
    {
        $valueDescription = $value !== null ? " with value '{$value}'" : '';
        expect($this->guessLocator($field, $value)->isChecked())->toBeFalse("Expected checkbox '{$field}'{$valueDescription} not to be checked, but it was.");

        return $this;
    }

    /**
     * Assert that the given checkbox is in an indeterminate state.
     */
    public function assertIndeterminate(string $field, ?string $value = null): Webpage
    {
        $this->assertNotChecked($field, $value);

        $selector = $this->guessLocator($field, $value)->selector();
        $escapedSelector = json_encode($selector);

        $isIndeterminate = $this->page->evaluate("
            () => {
                const element = document.querySelector({$escapedSelector});
                return element ? element.indeterminate === true : false;
            }
        ");

        $valueDescription = $value !== null ? " with value '{$value}'" : '';
        expect($isIndeterminate)->toBeTrue("Expected checkbox '{$field}'{$valueDescription} to be in indeterminate state, but it was not.");

        return $this;
    }

    /**
     * Assert that the given radio field is selected.
     */
    public function assertRadioSelected(string $field, string $value): Webpage
    {
        expect($this->guessLocator($field, $value)->isChecked())->toBeTrue("Expected radio button '{$field}' with value '{$value}' to be selected, but it was not.");

        return $this;
    }

    /**
     * Assert that the given radio field is not selected.
     */
    public function assertRadioNotSelected(string $field, ?string $value = null): Webpage
    {
        if ($value !== null) {
            expect($this->guessLocator($field, $value)->isChecked())->toBeFalse("Expected radio button '{$field}' with value '{$value}' not to be selected, but it was.");

            return $this;
        }

        $locator = $this->guessLocator($field);

        $count = $locator->count();

        $anyChecked = false;
        for ($i = 0; $i < $count; $i++) {
            $radio = $locator->nth($i);
            if ($radio->isChecked()) {
                $anyChecked = true;
                break;
            }
        }

        expect($anyChecked)->toBeFalse("Expected no radio buttons in group '{$field}' to be selected, but at least one was selected.");

        return $this;
    }

    /**
     * Assert that the given dropdown has the given value selected.
     */
    public function assertSelected(string $field, string $value): Webpage
    {
        $locator = $this->guessLocator($field);
        $actual = $locator->inputValue();

        expect($actual)->toBe($value, "Expected dropdown '{$field}' to have value '{$value}' selected, but found '{$actual}'.");

        return $this;
    }

    /**
     * Assert that the given dropdown does not have the given value selected.
     */
    public function assertNotSelected(string $field, string $value): Webpage
    {
        $locator = $this->guessLocator($field);
        $actual = $locator->inputValue();

        expect($actual)->not->toBe($value, "Expected dropdown '{$field}' not to have value '{$value}' selected, but it was.");

        return $this;
    }

    /**
     * Assert that the element matching the given selector has the given value.
     */
    public function assertValue(string $field, string $value): Webpage
    {
        $locator = $this->guessLocator($field);

        expect($actual = $locator->inputValue())->toBe($value, "Expected element '{$field}' to have value '{$value}', but found '{$actual}'.");

        return $this;
    }

    /**
     * Assert that the element matching the given selector does not have the given value.
     */
    public function assertValueIsNot(string $selector, string $value): Webpage
    {
        $actual = $this->guessLocator($selector)->inputValue();
        expect($actual)->not->toBe($value, "Expected element '{$selector}' not to have value '{$value}', but it did.");

        return $this;
    }

    /**
     * Assert that the element matching the given selector has the given value in the provided attribute.
     */
    public function assertAttribute(string $selector, string $attribute, string $value): Webpage
    {
        $actual = $this->guessLocator($selector)->getAttribute($attribute);
        expect($actual)->toBe($value, "Expected element '{$selector}' to have attribute '{$attribute}' with value '{$value}', but found '{$actual}'.");

        return $this;
    }

    /**
     * Assert that the element matching the given selector is missing the provided attribute.
     */
    public function assertAttributeMissing(string $selector, string $attribute): Webpage
    {
        $actual = $this->guessLocator($selector)->getAttribute($attribute);
        expect($actual)->toBeNull("Expected element '{$selector}' not to have attribute '{$attribute}', but it had value '{$actual}'.");

        return $this;
    }

    /**
     * Assert that the element matching the given selector contains the given value in the provided attribute.
     */
    public function assertAttributeContains(string $selector, string $attribute, string $value): Webpage
    {
        $attributeValue = $this->guessLocator($selector)->getAttribute($attribute);

        expect($attributeValue)->not->toBeNull("Expected element '{$selector}' to have attribute '{$attribute}', but it was not found.");

        $message = "Expected attribute '{$attribute}' of element '{$selector}' to contain '{$value}', but found '{$attributeValue}'.";
        expect(str_contains((string) $attributeValue, $value))->toBeTrue($message);

        return $this;
    }

    /**
     * Assert that the element matching the given selector does not contain the given value in the provided attribute.
     */
    public function assertAttributeDoesntContain(string $selector, string $attribute, string $value): Webpage
    {
        $attributeValue = $this->guessLocator($selector)->getAttribute($attribute);

        if ($attributeValue === null) {
            return $this;
        }

        $message = "Expected attribute '{$attribute}' of element '{$selector}' not to contain '{$value}', but found '{$attributeValue}'.";
        expect(str_contains($attributeValue, $value))->toBeFalse($message);

        return $this;
    }

    /**
     * Assert that the element matching the given selector has the given value in the provided aria attribute.
     */
    public function assertAriaAttribute(string $selector, string $attribute, string $value): Webpage
    {
        return $this->assertAttribute($selector, 'aria-'.$attribute, $value);
    }

    /**
     * Assert that the element matching the given selector has the given value in the provided data attribute.
     */
    public function assertDataAttribute(string $selector, string $attribute, string $value): Webpage
    {
        return $this->assertAttribute($selector, 'data-'.$attribute, $value);
    }

    /**
     * Assert that the element matching the given selector is visible.
     */
    public function assertVisible(string $selector): Webpage
    {
        $locator = $this->guessLocator($selector);

        expect($locator->isVisible())->toBeTrue("Expected element '{$selector}' to be visible, but it was not.");

        return $this;
    }

    /**
     * Assert that the element matching the given selector is present.
     */
    public function assertPresent(string $selector): Webpage
    {
        $count = $this->guessLocator($selector)->count();
        expect($count)->toBeGreaterThan(0, "Expected element '{$selector}' to be present in the DOM, but it was not found.");

        return $this;
    }

    /**
     * Assert that the element matching the given selector is not present in the source.
     */
    public function assertNotPresent(string $selector): Webpage
    {
        $count = $this->guessLocator($selector)->count();
        expect($count)->toBe(0, "Expected element '{$selector}' not to be present in the DOM, but it was found.");

        return $this;
    }

    /**
     * Assert that the element matching the given selector is not visible.
     */
    public function assertMissing(string $selector): Webpage
    {
        $locator = $this->guessLocator($selector);

        expect($locator->isVisible())->toBeFalse("Expected element '{$selector}' not to be visible, but it was.");

        return $this;
    }

    /**
     * Assert that the given field is enabled.
     */
    public function assertEnabled(string $field): Webpage
    {
        expect($this->guessLocator($field)->isEnabled())->toBeTrue("Expected field '{$field}' to be enabled, but it was disabled.");

        return $this;
    }

    /**
     * Assert that the given field is disabled.
     */
    public function assertDisabled(string $field): Webpage
    {
        expect($this->guessLocator($field)->isDisabled())->toBeTrue("Expected field '{$field}' to be disabled, but it was enabled.");

        return $this;
    }

    /**
     * Assert that the given button is enabled.
     */
    public function assertButtonEnabled(string $button): Webpage
    {
        $selector = $this->guessLocator($button);

        expect($selector->isEnabled())->toBeTrue("Expected button '{$button}' to be enabled, but it was disabled.");

        return $this;
    }

    /**
     * Assert that the given button is disabled.
     */
    public function assertButtonDisabled(string $button): Webpage
    {
        $selector = $this->guessLocator($button);

        expect($selector->isDisabled())->toBeTrue("Expected button '{$button}' to be disabled, but it was enabled.");

        return $this;
    }
}
