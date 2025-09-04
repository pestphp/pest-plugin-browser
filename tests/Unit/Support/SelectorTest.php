<?php

declare(strict_types=1);

use Pest\Browser\Support\Selector;

it('returns correct selector without exact match', function (): void {
    $selector = Selector::getByAttributeTextSelector('data-test', 'example', false);

    expect($selector)->toBe('internal:attr=[data-test="example"i]');
});

it('returns correct selector with exact match', function (): void {
    $selector = Selector::getByAttributeTextSelector('data-test', 'example', true);

    expect($selector)->toBe('internal:attr=[data-test="example"]');
});

it('escapes special characters in attribute values', function (): void {
    $selector = Selector::getByAttributeTextSelector('data-test', 'example "quoted" text', false);

    expect($selector)->toBe('internal:attr=[data-test="example \"quoted\" text"i]');
});

it('escapes backslashes in attribute values', function (): void {
    $selector = Selector::getByAttributeTextSelector('data-test', 'example\\path', false);

    expect($selector)->toBe('internal:attr=[data-test="example\\\\path"i]');
});

it('returns correct selector for test ID', function (): void {
    $selector = Selector::getByTestIdSelector('data-testid', 'login-button');

    expect($selector)->toBe('internal:testid=[data-testid="login-button"]');
});

it('escapes special characters in test ID', function (): void {
    $selector = Selector::getByTestIdSelector('data-testid', 'button"with"quotes');

    expect($selector)->toBe('internal:testid=[data-testid="button\"with\"quotes"]');
});

it('returns correct selector without exact match for label', function (): void {
    $selector = Selector::getByLabelSelector('Email address', false);

    expect($selector)->toBe('internal:label="Email address"i');
});

it('returns correct selector with exact match for label', function (): void {
    $selector = Selector::getByLabelSelector('Email address', true);

    expect($selector)->toBe('internal:label="Email address"s');
});

it('returns correct selector without exact match for alt text', function (): void {
    $selector = Selector::getByAltTextSelector('Logo image', false);

    expect($selector)->toBe('internal:attr=[alt="Logo image"i]');
});

it('returns correct selector with exact match for alt text', function (): void {
    $selector = Selector::getByAltTextSelector('Logo image', true);

    expect($selector)->toBe('internal:attr=[alt="Logo image"]');
});

it('returns correct selector without exact match for title', function (): void {
    $selector = Selector::getByTitleSelector('Information', false);

    expect($selector)->toBe('internal:attr=[title="Information"i]');
});

it('returns correct selector with exact match for title', function (): void {
    $selector = Selector::getByTitleSelector('Information', true);

    expect($selector)->toBe('internal:attr=[title="Information"]');
});

it('returns correct selector without exact match for placeholder', function (): void {
    $selector = Selector::getByPlaceholderSelector('Search...', false);

    expect($selector)->toBe('internal:attr=[placeholder="Search..."i]');
});

it('returns correct selector with exact match for placeholder', function (): void {
    $selector = Selector::getByPlaceholderSelector('Search...', true);

    expect($selector)->toBe('internal:attr=[placeholder="Search..."]');
});

it('returns correct selector without exact match for text', function (): void {
    $selector = Selector::getByTextSelector('Submit form', false);

    expect($selector)->toBe('internal:text="Submit form"i');
});

it('returns correct selector with exact match for text', function (): void {
    $selector = Selector::getByTextSelector('Submit form', true);

    expect($selector)->toBe('internal:text="Submit form"s');
});

it('escapes special regex characters', function (): void {
    $escaped = Selector::escapeForRegex('text+with(special)[regex]{chars}.*$^');

    expect($escaped)->toBe('text\+with\(special\)\[regex\]\{chars\}\.\*\$\^');
});

it('returns JSON-encoded string with "i" suffix for non-exact match', function (): void {
    $escaped = Selector::escapeForTextSelector('Search text', false);

    expect($escaped)->toBe('"Search text"i');
});

it('returns JSON-encoded string with "s" suffix for exact match', function (): void {
    $escaped = Selector::escapeForTextSelector('Search text', true);

    expect($escaped)->toBe('"Search text"s');
});

it('properly escapes quotes in the text', function (): void {
    $escaped = Selector::escapeForTextSelector('Text with "quotes"', false);

    expect($escaped)->toBe('"Text with \"quotes\""i');
});

it('handles simple strings', function (): void {
    $escaped = Selector::escapeForAttributeSelector('simple');

    expect($escaped)->toBe('"simple"i');
});

it('escapes backslashes', function (): void {
    $escaped = Selector::escapeForAttributeSelector('text\\with\\backslashes');

    expect($escaped)->toBe('"text\\\\with\\\\backslashes"i');
});

it('escapes quotes', function (): void {
    $escaped = Selector::escapeForAttributeSelector('text"with"quotes');

    expect($escaped)->toBe('"text\"with\"quotes"i');
});

it('uses "i" suffix for non-exact match', function (): void {
    $escaped = Selector::escapeForAttributeSelector('text', false);

    expect($escaped)->toBe('"text"i');
});

it('omits "i" suffix for exact match', function (): void {
    $escaped = Selector::escapeForAttributeSelector('text', true);

    expect($escaped)->toBe('"text"');
});

it('returns basic role selector without options', function (): void {
    $selector = Selector::getByRoleSelector('button');

    expect($selector)->toBe('internal:role=button');
});

it('handles boolean options correctly', function (): void {
    $selector = Selector::getByRoleSelector('checkbox', [
        'checked' => true,
        'disabled' => false,
    ]);

    expect($selector)->toBe('internal:role=checkbox[checked=true][disabled=false]');
});

it('handles name option without exact flag', function (): void {
    $selector = Selector::getByRoleSelector('button', [
        'name' => 'Submit form',
    ]);

    expect($selector)->toBe('internal:role=button[name="Submit form"i]');
});

it('handles name option with exact flag', function (): void {
    $selector = Selector::getByRoleSelector('button', [
        'name' => 'Submit form',
        'exact' => true,
    ]);

    expect($selector)->toBe('internal:role=button[name="Submit form"]');
});

it('handles all possible options', function (): void {
    $selector = Selector::getByRoleSelector('combobox', [
        'checked' => true,
        'disabled' => false,
        'selected' => true,
        'expanded' => true,
        'includeHidden' => false,
        'level' => 2,
        'name' => 'Country selector',
        'pressed' => false,
    ]);

    expect($selector)->toBe('internal:role=combobox[checked=true][disabled=false][selected=true][expanded=true][include-hidden=false][level=2][name="Country selector"i][pressed=false]');
});

it('escapes special characters in name option', function (): void {
    $selector = Selector::getByRoleSelector('button', [
        'name' => 'Button "with" quotes\\backslashes',
    ]);

    expect($selector)->toBe('internal:role=button[name="Button \"with\" quotes\\\\backslashes"i]');
});

it('properly detects explicit selectors', function (): void {
    expect(Selector::isExplicit('#test'))->toBeTrue()
        ->and(Selector::isExplicit('.test'))->toBeTrue()
        ->and(Selector::isExplicit('[test]'))->toBeTrue()
        ->and(Selector::isExplicit('[name*="test"]'))->toBeTrue()
        ->and(Selector::isExplicit('[name^="test"]'))->toBeTrue()
        ->and(Selector::isExplicit('[name$="test"]'))->toBeTrue()
        ->and(Selector::isExplicit('button[name="test"]'))->toBeTrue()

        // CSS combinators
        ->and(Selector::isExplicit('div > p'))->toBeTrue()
        ->and(Selector::isExplicit('h1 + p'))->toBeTrue()
        ->and(Selector::isExplicit('h1 ~ p'))->toBeTrue()

        // Pseudo-classes and pseudo-elements
        ->and(Selector::isExplicit('a:hover'))->toBeTrue()
        ->and(Selector::isExplicit('p::first-line'))->toBeTrue()
        ->and(Selector::isExplicit('input:nth-child(2n)'))->toBeTrue()

        // Universal selector and other special chars
        ->and(Selector::isExplicit('*'))->toBeTrue()
        ->and(Selector::isExplicit('input|text'))->toBeTrue()

        // Selector lists
        ->and(Selector::isExplicit('a, b'))->toBeTrue()

        // Internal selectors
        ->and(Selector::isExplicit('internal:test'))->toBeTrue()

        // Plain text should be false
        ->and(Selector::isExplicit('test'))->toBeFalse()
        ->and(Selector::isExplicit('Click Me Button'))->toBeFalse()
        ->and(Selector::isExplicit('Submit Form'))->toBeFalse();
});

it('properly detects data-test selectors', function (): void {
    expect(Selector::isDataTest('@test'))->toBeTrue()
        ->and(Selector::isDataTest('test'))->toBeFalse();
});
