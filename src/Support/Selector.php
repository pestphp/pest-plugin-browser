<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

final class Selector
{
    /**
     * Check if the selector is a selector.
     */
    public static function isExplicit(string $selector): bool
    {
        foreach (['#', '.', '[', 'internal:'] as $s) {
            if (str_starts_with($selector, $s)) {
                return true;
            }
        }

        if (str_ends_with($selector, '[]')) {
            return false;
        }

        $cssSpecialChars = ['[', ']', '#', '>', '+', '~', ':', '*', '|', '^', ',', '=', ',', '(', ')'];

        foreach ($cssSpecialChars as $cssSpecialChar) {
            if (str_contains($selector, $cssSpecialChar)) {
                return true;
            }
        }

        // A period is a CSS selector if it's followed by a valid CSS class name pattern
        return (bool) preg_match('/\.[a-zA-Z_-][a-zA-Z0-9_-]*/', $selector);
    }

    /**
     * Check if the selector is a data test selector.
     */
    public static function isDataTest(string $selector): bool
    {
        return str_starts_with($selector, '@');
    }

    /**
     * Get selector by attribute text.
     */
    public static function getByAttributeTextSelector(string $attrName, string $text, bool $exact = false): string
    {
        return 'internal:'."attr=[{$attrName}=".self::escapeForAttributeSelectorOrRegex($text, $exact).']';
    }

    /**
     * Get selector by test ID.
     */
    public static function getByTestIdSelector(string $testIdAttributeName, string $testId): string
    {
        return 'internal:'."testid=[{$testIdAttributeName}=".self::escapeForAttributeSelectorOrRegex($testId, true).']';
    }

    /**
     * Get selector by label.
     */
    public static function getByLabelSelector(string $text, bool $exact): string
    {
        return 'internal:label='.self::escapeForTextSelector($text, $exact);
    }

    /**
     * Get selector by alt text.
     */
    public static function getByAltTextSelector(string $text, bool $exact): string
    {
        return self::getByAttributeTextSelector('alt', $text, $exact);
    }

    /**
     * Get selector by title.
     */
    public static function getByTitleSelector(string $text, bool $exact): string
    {
        return self::getByAttributeTextSelector('title', $text, $exact);
    }

    /**
     * Get selector by placeholder.
     */
    public static function getByPlaceholderSelector(string $text, bool $exact): string
    {
        return self::getByAttributeTextSelector('placeholder', $text, $exact);
    }

    /**
     * Get selector by text.
     */
    public static function getByTextSelector(string $text, bool $exact): string
    {
        return 'internal:text='.self::escapeForTextSelector($text, $exact);
    }

    /**
     * Escape text for regex.
     */
    public static function escapeForRegex(string $text): string
    {
        return preg_quote($text, '/');
    }

    /**
     * Escape for text selector.
     */
    public static function escapeForTextSelector(string $text, bool $exact = false): string
    {
        if ($exact) {
            return json_encode($text).'s';
        }

        return json_encode($text).'i';
    }

    /**
     * Escape for attribute selector or regex.
     */
    public static function escapeForAttributeSelectorOrRegex(string $text, bool $exact = false): string
    {
        return self::escapeForAttributeSelector($text, $exact);
    }

    /**
     * Escape for attribute selector.
     */
    public static function escapeForAttributeSelector(string $text, bool $exact = false): string
    {
        $escapedText = str_replace('\\', '\\\\', $text);
        $escapedText = str_replace('"', '\\"', $escapedText);

        if ($exact) {
            return "\"{$escapedText}\"";
        }

        return "\"{$escapedText}\"i";

    }

    /**
     * Get selector by role.
     *
     * @param  array<string, string|bool>  $options
     */
    public static function getByRoleSelector(string $role, array $options = []): string
    {
        $props = [];

        if (isset($options['checked'])) {
            $props['checked'] = (bool) $options['checked'] ? 'true' : 'false';
        }
        if (isset($options['disabled'])) {
            $props['disabled'] = (bool) $options['disabled'] ? 'true' : 'false';
        }
        if (isset($options['selected'])) {
            $props['selected'] = (bool) $options['selected'] ? 'true' : 'false';
        }
        if (isset($options['expanded'])) {
            $props['expanded'] = (bool) $options['expanded'] ? 'true' : 'false';
        }
        if (isset($options['includeHidden'])) {
            $props['include-hidden'] = (bool) $options['includeHidden'] ? 'true' : 'false';
        }
        if (isset($options['level'])) {
            $props['level'] = (string) $options['level'];
        }
        if (isset($options['name'])) {
            $exact = $options['exact'] ?? false;
            $props['name'] = self::escapeForAttributeSelector((string) $options['name'], (bool) $exact);
        }
        if (isset($options['pressed'])) {
            $props['pressed'] = (bool) $options['pressed'] ? 'true' : 'false';
        }

        $propsStr = '';
        foreach ($props as $k => $v) {
            $propsStr .= '['.$k.'='.$v.']';
        }

        return 'internal:'."role={$role}{$propsStr}";
    }
}
