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

it('should not have the title "Laravel Dusk"', function () {
    visit('https://laravel.com')
        ->toNotHaveTitle('Laravel Dusk');
});
