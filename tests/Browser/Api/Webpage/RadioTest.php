<?php

declare(strict_types=1);

it('may select a radio button', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="radio" id="male" name="gender" value="male">
            <label for="male">Male</label>
            <input type="radio" id="female" name="gender" value="female">
            <label for="female">Female</label>
        </form>
    ');

    $page = visit('/');

    $page->radio('gender', 'female');

    // Check that the female radio button is checked
    expect($page->script('() => document.querySelector("input[name=gender][value=female]").checked'))->toBeTrue();
    expect($page->script('() => document.querySelector("input[name=gender][value=male]").checked'))->toBeFalse();
});

it('may select a radio button by id', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <input type="radio" id="option1" name="option" value="1">
            <label for="option1">Option 1</label>
            <input type="radio" id="option2" name="option" value="2">
            <label for="option2">Option 2</label>
        </form>
    ');

    $page = visit('/');

    $page->radio('#option2', '2');

    // Check that option2 is checked
    expect($page->script('() => document.querySelector("#option2").checked'))->toBeTrue();
    expect($page->script('() => document.querySelector("#option1").checked'))->toBeFalse();
});
