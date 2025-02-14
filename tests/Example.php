<?php

declare(strict_types=1);

use function Pest\Browser\visit;

it('may have a title at playwright', function () {
    visit('https://playwright.dev/')
        ->toHaveTitle('Fast and reliable end-to-end testing for modern web apps | Playwright')
        ->toHaveTitle('/testing/')
        ->toHaveTitle('/.*Playwright$/');
});

it('may have a title at laravel', function () {
    visit('https://laravel.com')
        ->toHaveTitle('Laravel - The PHP Framework For Web Artisans')
        ->toHaveTitle('/Framework/')
        ->toHaveTitle('/.*Artisans$/');
});

it('may click the "get started" button at laravel', function () {
    visit('https://laravel.com')
        ->clickLink('Get Started')
        ->assertUrlIs('https://laravel.com/docs/11.x');
});

it('should not have the title "Laravel Dusk"', function () {
    visit('https://laravel.com')
        ->toNotHaveTitle('Laravel Dusk');
});

it('may have a url for laravel', function () {
    visit('https://laravel.com')
        ->toHaveUrl('https://laravel.com/')
        ->toHaveUrl('/^https/')
        ->toHaveUrl('/\.com\/?$/');
});
