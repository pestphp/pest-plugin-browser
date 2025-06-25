<?php

declare(strict_types=1);

it('may uncheck a checkbox', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="checkbox" id="terms" name="terms" checked>
            <label for="terms">I agree to the terms</label>
        </form>
    ');

    $page = visit('/');

    $page->uncheck('terms');

    // Check that the checkbox is unchecked
    expect($page->script('() => document.querySelector("input[name=terms]").checked'))->toBeFalse();
});

it('may uncheck a checkbox that is already unchecked', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="checkbox" id="terms" name="terms">
            <label for="terms">I agree to the terms</label>
        </form>
    ');

    $page = visit('/');

    $page->uncheck('terms');

    // Check that the checkbox is still unchecked
    expect($page->script('() => document.querySelector("input[name=terms]").checked'))->toBeFalse();
});

it('may uncheck a checkbox with a specific value', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="checkbox" id="option1" name="options" value="1" checked>
            <label for="option1">Option 1</label>
            <input type="checkbox" id="option2" name="options" value="2" checked>
            <label for="option2">Option 2</label>
        </form>
    ');

    $page = visit('/');

    $page->uncheck('options', '2');

    // Check that only the second checkbox is unchecked
    expect($page->script('() => document.querySelector("input[name=\'options\'][value=\'1\']").checked'))->toBeTrue();
    expect($page->script('() => document.querySelector("input[name=\'options\'][value=\'2\']").checked'))->toBeFalse();
});
