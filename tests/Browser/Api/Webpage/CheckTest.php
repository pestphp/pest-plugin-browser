<?php

declare(strict_types=1);

it('may check a checkbox', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="checkbox" id="terms" name="terms">
            <label for="terms">I agree to the terms</label>
        </form>
    ');

    $page = visit('/');

    $page->check('terms');

    // Check that the checkbox is checked
    expect($page->script('() => document.querySelector("input[name=terms]").checked'))->toBeTrue();
});

it('may check a checkbox that is already checked', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="checkbox" id="terms" name="terms" checked>
            <label for="terms">I agree to the terms</label>
        </form>
    ');

    $page = visit('/');

    $page->check('terms');

    // Check that the checkbox is still checked
    expect($page->script('() => document.querySelector("input[name=terms]").checked'))->toBeTrue();
});

it('may check a checkbox with a specific value', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="checkbox" id="option1" name="options[]" value="1">
            <label for="option1">Option 1</label>
            <input type="checkbox" id="option2" name="options[]" value="2">
            <label for="option2">Option 2</label>
        </form>
    ');

    $page = visit('/');

    $page->check('options[]', '2');

    // Check that only the second checkbox is checked
    expect($page->script('() => document.querySelector("input[name=\'options[]\'][value=\'2\']").checked'))->toBeTrue();
    expect($page->script('() => document.querySelector("input[name=\'options[]\'][value=\'1\']").checked'))->toBeFalse();
});
