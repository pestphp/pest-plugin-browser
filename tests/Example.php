<?php

use function Pest\Browser\visit;
use Pest\Browser\Locators\GetByRole;

it('may have a title at playwright', function () {
    visit('https://playwright.dev/')
        ->toHaveTitle('/Playwright/')
        ->toHaveTitle('Fast and reliable end-to-end testing for modern web apps | Playwright');
});

it('may have a title at laravel', function () {
    visit('https://laravel.com')
        ->toHaveTitle('/Laravel/');
});

it('has no title pest', function () {
    visit('https://laravel.com')
        ->doesntHaveTitle('Pest');
});

it('has laravel in URL', function () {
    visit('https://laravel.com')
        ->toHaveURL('.*laravel.com');
});

it('click link laracasts', function () {
    visit('https://laravel.com')
        ->click(GetByRole::class, 'link', 'Watch Laracasts');
});
