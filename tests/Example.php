<?php

use function Pest\Browser\visit;

it('may have a title at playwright', function () {
    visit('https://playwright.dev/')
        ->toHaveTitle('Playwright');
});

it('may have a title at laravel', function () {
    visit('https://laravel.com')
        ->toHaveTitle('Laravel');
});

it('may click the "get started" button at laravel', function () {
    visit('https://laravel.com')
        ->clickLink('Get Started')
        ->assertUrlIs('https://laravel.com/docs/11.x');

    // -- or --

    $browser = visit('https://laravel.com')
        ->clickLink('Get Started');

    expect($browser->response()->url())->toBe('https://laravel.com/docs/11.x');
});
