<?php

declare(strict_types=1);

beforeEach()->skip();

it('may select an option by value', function (): void {
    Route::get('/', fn (): string => '
        <select name="country">
            <option value="us">United States</option>
            <option value="ca">Canada</option>
            <option value="mx">Mexico</option>
        </select>
    ');

    $page = visit('/');

    $page->assertValue('country', 'us');

    $page->select('country', 'ca');

    $page->assertValue('country', 'ca');
});

it('may select multiple options', function (): void {
    Route::get('/', fn (): string => '
        <select id="countries" name="countries[]" multiple>
            <option value="us">United States</option>
            <option value="ca">Canada</option>
            <option value="mx">Mexico</option>
        </select>
    ');

    $page = visit('/');

    $page->select('#countries', ['us', 'ca']);

    // Check that both options are selected
    $selectedOptions = $page->script('() => {
        const select = document.querySelector("#countries");
        return Array.from(select.options)
            .filter(option => option.selected)
            .map(option => option.value);
    }');
    expect($selectedOptions)->toBe(['us', 'ca']);
});

it('may select a random option when no value is provided', function (): void {
    Route::get('/', fn (): string => '
        <select name="country">
            <option value="us">United States</option>
            <option value="ca">Canada</option>
            <option value="mx">Mexico</option>
        </select>
    ');

    $page = visit('/');

    $page->value('#country');

    // Check that some option is selected
    $value = $page->value('country');
    expect($value)->toBeIn(['us', 'ca', 'mx']);
});
