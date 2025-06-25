<?php

declare(strict_types=1);

it('may press a button and wait for it to be enabled again', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <button type="button" id="submit-btn" onclick="this.disabled = true; setTimeout(() => { this.disabled = false; document.getElementById(\'result\').textContent = \'Button Enabled\'; }, 100);">Submit</button>
            <div id="result"></div>
        </form>
    ');

    $page = visit('/');

    $page->pressAndWaitFor('Submit', 0.2);

    expect($page->text('#result'))->toBe('Button Enabled');
    expect($page->script('() => document.getElementById("submit-btn").disabled'))->toBeFalse();
});

it('may press a button and wait for it with a custom timeout', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <button type="button" id="submit-btn" onclick="this.disabled = true; setTimeout(() => { this.disabled = false; document.getElementById(\'result\').textContent = \'Button Enabled\'; }, 100);">Submit</button>
            <div id="result"></div>
        </form>
    ');

    $page = visit('/');

    $page->pressAndWaitFor('Submit', 0.2);

    expect($page->text('#result'))->toBe('Button Enabled');
    expect($page->script('() => document.getElementById("submit-btn").disabled'))->toBeFalse();
});

it('may press a button with an id and wait for it', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <button type="button" id="submit-btn" onclick="this.disabled = true; setTimeout(() => { this.disabled = false; document.getElementById(\'result\').textContent = \'Button Enabled\'; }, 100);">Submit</button>
            <div id="result"></div>
        </form>
    ');

    $page = visit('/');

    $page->pressAndWaitFor('submit-btn', 0.2);

    expect($page->text('#result'))->toBe('Button Enabled')
        ->and($page->script('() => document.getElementById("submit-btn").disabled'))->toBeFalse();
});
